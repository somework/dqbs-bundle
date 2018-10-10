<?php

namespace Strongknit\DqbsBundle;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class DqbSearch
{
    const LOGIC_OR = 'OR';

    const LOGIC_AND = 'AND';

    protected $searchItems = [];

    protected $qb;

    protected $filtersCounter;

    protected $qbTablesAliases;

    /**
     * @param DqbSearchItem $searchItem
     * @return DqbSearch
     */
    public function addSearchItem(DqbSearchItem $searchItem)
    {
        $this->searchItems[] = $searchItem;

        return $this;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $logic
     * @return $this
     * @throws DqbsBundleException
     */
    public function applySearch(QueryBuilder $qb, $logic = self::LOGIC_AND)
    {
        $this->filtersCounter = 0;
        $this->qb = $qb;
        $this->getQbTablesAliases();
        $data = $this->getWhere();
        if (self::LOGIC_AND === mb_strtoupper($logic)) {
            $this->qb->andWhere($data['where']);
        } else {
            $this->qb->orWhere($data['where']);
        }
        foreach ($data['params'] as $param) {
            $this->qb->setParameter($param['name'], $param['value']);
        }

        return $this;
    }

    /**
     * @throws DqbsBundleException
     */
    protected function getWhere()
    {
        $result = [
            'where' => '',
            'params' => [],
        ];
        foreach ($this->searchItems as $item) {
            $itemData = $this->getWhereForItem($item);
            $result['where'] .= '('.$itemData['where'].')'.' OR ';
            $result['params'] = array_merge($result['params'], $itemData['params']);
        }
        $result['where'] = rtrim($result['where'], ' OR ');

        return $result;
    }

    /**
     * @param DqbSearchItem $item
     * @return array
     * @throws DqbsBundleException
     */
    protected function getWhereForItem(DqbSearchItem $item)
    {
        $this->filtersCounter++;

        $itemClassName = $this->qbTablesAliases[$item->getEntityAlias()] ?? null;
        if (!$itemClassName) {
            throw new DqbsBundleException("Table with alias '{$item->getEntityAlias()}' does not found in query builder");
        }
        $metadata = $this->qb->getEntityManager()->getClassMetadata($itemClassName);

        $entityFields = $metadata->getFieldNames();
        if ($diff = array_diff($item->getIncludedFields(), $entityFields)) {
            $diff = implode($diff, ',');
            throw new DqbsBundleException(sprintf('No fields "%s" in the entity "%s"', $diff, $item->getEntityAlias()));
        }
        if ($diff = array_diff($item->getExcludedFields(), $entityFields)) {
            $diff = implode($diff, ',');
            throw new DqbsBundleException(sprintf('No fields "%s" in the entity "%s".', $diff, $item->getEntityAlias()));
        }

        $includeFields = $item->getIncludedFields() ?: $entityFields;
        $includeFields = array_diff($includeFields, $item->getExcludedFields());

        $useFilterT = $useFilterI = false;
        $tParamName = 'tFilter'.$this->filtersCounter;
        $iParamName = 'iFilter'.$this->filtersCounter;

        $where = '';
        $params = [];
        foreach ($includeFields as $incF) {
            $fieldType = $metadata->getTypeOfField($incF);
            if (in_array($fieldType, [Type::JSON_ARRAY])) {
                $useFilterT = true;
                $where .= '(UPPER(CAST('.$item->getEntityAlias().'.'.$incF." AS TEXT)) like UPPER(:$tParamName)) OR ";
            } elseif (in_array($fieldType, [Type::STRING, Type::TEXT])) {
                $useFilterT = true;
                $where .= '(UPPER('.$item->getEntityAlias().'.'.$incF.") like UPPER(:$tParamName)) OR ";
            } elseif (in_array($fieldType, [Type::BIGINT, Type::INTEGER, Type::SMALLINT]) && is_numeric($item->getSearchValue())) {
                $useFilterI = true;
                $where .= '('.$item->getEntityAlias().'.'.$incF." = :$iParamName) OR ";
            }
        }

        if ($useFilterT) {
            $params[] = [
                'name' => $tParamName,
                'value' => "%{$item->getSearchValue()}%",
            ];
        }

        if ($useFilterI) {
            $params[] = [
                'name' => $iParamName,
                'value' => (int) $item->getSearchValue(),
            ];
        }

        $where = rtrim($where, " OR ");

        return [
            'where' => $where,
            'params' => $params,
        ];
    }

    /**
     *
     */
    protected function getQbTablesAliases()
    {
        $this->qbTablesAliases = [];
        /** @var From $part */
        foreach ($this->qb->getDQLPart('from') as $part) {
            $this->qbTablesAliases[$part->getAlias()] = $part->getFrom();
            $joins = $this->qb->getDQLPart('join');
            $joins = reset($joins);
            $this->getAssociation($part->getAlias(), $part->getFrom(), $joins);
        }
    }

    /**
     * @param string $baseAlias
     * @param string $className
     * @param Join[] $joins
     */
    protected function getAssociation($baseAlias, $className, $joins)
    {
        $associations = $this->qb->getEntityManager()
            ->getClassMetadata($className)
            ->getAssociationMappings();
        foreach ($associations as $alias => $data) {
            foreach ($joins as $join) {
                if ($join->getJoin() == $baseAlias.'.'.$alias) {
                    if (!isset($result[$join->getAlias()])) {
                        $this->qbTablesAliases[$join->getAlias()] = $data['targetEntity'];
                        $this->getAssociation($join->getAlias(), $data['targetEntity'], $joins);
                    }
                }
            }
        }
    }
}
