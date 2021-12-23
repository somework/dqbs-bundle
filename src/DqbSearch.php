<?php

namespace Strongknit\DqbsBundle;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class DqbSearch
{
    public const LOGIC_OR = 'OR';

    public const LOGIC_AND = 'AND';

    public const AVAILABLE_LOGIC = [self::LOGIC_AND, self::LOGIC_OR];

    protected $searchItems = [];

    protected $qb;

    protected $searchItemsCounter;

    protected $qbTablesAliases;

    protected $searchValue;

    /**
     * @param DqbSearchItem $searchItem
     * @return DqbSearch
     */
    public function addSearchItem(DqbSearchItem $searchItem): DqbSearch
    {
        $this->searchItems[] = $searchItem;

        return $this;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $searchValue
     * @param string       $logic
     * @return $this
     * @throws DqbsBundleException
     */
    public function applySearch(QueryBuilder $qb, string $searchValue = '', string $logic = self::LOGIC_AND): DqbSearch
    {
        if (!in_array($logic, self::AVAILABLE_LOGIC)) {
            throw new DqbsBundleException("Unavailable logic '$logic'");
        }
        $this->searchItemsCounter = 0;
        $this->searchValue = $searchValue;
        $this->qb = $qb;
        $this->collectTableAliasesFromQb();
        $this->prepareSearchItems();
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
    protected function prepareSearchItems(): void
    {
        if (!$this->searchItems && '' == $this->searchValue) {
            throw new DqbsBundleException("Search value does not set.");
        }

        if (!$this->searchItems) {
            foreach ($this->qbTablesAliases as $alias => $class) {
                $this->searchItems[] = new DqbSearchItem($alias);
            }
        }

        foreach ($this->searchItems as $searchItem) {
            if ('' == $this->searchValue && '' == $searchItem->getSearchValue()) {
                throw new DqbsBundleException("Search value does not set.");
            }

            $searchItem->setSearchValue($this->searchValue);
        }
    }

    /**
     * @throws DqbsBundleException
     */
    protected function getWhere(): array
    {
        $result = [
            'params' => [],
        ];

        $whereParts = [];
        foreach ($this->searchItems as $item) {
            $itemData = $this->getWhereForSearchItem($item);
            $whereParts[] = '('.$itemData['where'].')';
            $result['params'] = array_merge($result['params'], $itemData['params']);
        }
        $result['where'] = implode(' OR ', $whereParts);

        return $result;
    }

    /**
     * @param DqbSearchItem $item
     * @return array
     * @throws DqbsBundleException
     */
    protected function getWhereForSearchItem(DqbSearchItem $item): array
    {
        $this->searchItemsCounter++;

        $itemClassName = $this->qbTablesAliases[$item->getEntityAlias()] ?? null;
        if (!$itemClassName) {
            throw new DqbsBundleException(
                "Table with alias '{$item->getEntityAlias()}' does not found in query builder"
            );
        }
        $metadata = $this->qb->getEntityManager()->getClassMetadata($itemClassName);

        $entityFields = $metadata->getFieldNames();
        if ($diff = array_diff($item->getIncludedFields(), $entityFields)) {
            $diff = implode(',', $diff);
            throw new DqbsBundleException(
                sprintf('No fields found in the entity "%s": "%s"', $item->getEntityAlias(), $diff)
            );
        }
        if ($diff = array_diff($item->getExcludedFields(), $entityFields)) {
            $diff = implode(',', $diff);
            throw new DqbsBundleException(
                sprintf('No fields found in the entity "%s": "%s"', $item->getEntityAlias(), $diff)
            );
        }

        $includeFields = $item->getIncludedFields() ?: $entityFields;
        $includeFields = array_diff($includeFields, $item->getExcludedFields());

        $useFilterT = $useFilterI = false;
        $tParamName = 'tFilter'.$this->searchItemsCounter;
        $iParamName = 'iFilter'.$this->searchItemsCounter;

        $params = [];

        $whereParts = [];
        foreach ($includeFields as $incF) {
            $fieldType = $metadata->getTypeOfField($incF);
            if ($fieldType === Types::JSON) {
                $useFilterT = true;
                $whereParts[] = '(UPPER(CAST('.$item->getEntityAlias().'.'.$incF." AS TEXT)) like UPPER(:$tParamName))";
            } elseif (in_array($fieldType, [Types::STRING, Types::TEXT], true)) {
                $useFilterT = true;
                $whereParts[] = '(UPPER('.$item->getEntityAlias().'.'.$incF.") like UPPER(:$tParamName))";
            } elseif (in_array($fieldType, [Types::BIGINT, Types::INTEGER, Types::SMALLINT], true) && is_numeric(
                    $item->getSearchValue()
                )) {
                $useFilterI = true;
                $whereParts[] = '('.$item->getEntityAlias().'.'.$incF." = :$iParamName)";
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
                'value' => (int)$item->getSearchValue(),
            ];
        }

        return [
            'where' => implode(' OR ', $whereParts),
            'params' => $params,
        ];
    }

    /**
     *
     */
    protected function collectTableAliasesFromQb(): void
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
    protected function getAssociation(string $baseAlias, string $className, array $joins): void
    {
        if (!$joins) {
            return;
        }

        $associations = $this->qb->getEntityManager()
            ->getClassMetadata($className)
            ->getAssociationMappings();
        foreach ($associations as $alias => $data) {
            foreach ($joins as $join) {
                if ($join->getJoin() == $baseAlias.'.'.$alias) {
//                    if (!isset($result[$join->getAlias()])) {
                    $this->qbTablesAliases[$join->getAlias()] = $data['targetEntity'];
                    $this->getAssociation($join->getAlias(), $data['targetEntity'], $joins);
//                    }
                }
            }
        }
    }
}
