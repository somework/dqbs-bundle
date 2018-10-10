<?php

namespace Strongknit\DqbsBundle;

class DqbSearchItem
{
    protected $entityAlias;

    protected $searchValue;

    protected $searchValueIgnorePrefix;

    protected $searchByEntityFields;

    protected $includedFields;

    protected $excludedFields;

    /**
     * SearchItem constructor.
     * @param string $entityAlias
     * @param string $searchValue
     */
    public function __construct($entityAlias, $searchValue)
    {
        $this->entityAlias = $entityAlias;
        $this->searchValue = $searchValue;
        $this->searchByEntityFields = true;
        $this->includedFields = [];
        $this->excludedFields = [];
    }

    /**
     * @return string
     */
    public function getEntityAlias()
    {
        return $this->entityAlias;
    }

    /**
     * @param string $entityAlias
     * @return DqbSearchItem
     */
    public function setEntityAlias($entityAlias)
    {
        $this->entityAlias = $entityAlias;

        return $this;
    }

    /**
     * @return string
     */
    public function getSearchValue()
    {
        return $this->searchValue;
    }

    /**
     * @param string $searchValue
     * @return DqbSearchItem
     */
    public function setSearchValue($searchValue)
    {
        $this->searchValue = $searchValue;

        return $this;
    }

    /**
     * @return string
     */
    public function getSearchValueIgnorePrefix()
    {
        return $this->searchValueIgnorePrefix;
    }

    /**
     * @param string $searchValueIgnorePrefix
     * @return DqbSearchItem
     */
    public function setSearchValueIgnorePrefix($searchValueIgnorePrefix)
    {
        $this->searchValueIgnorePrefix = $searchValueIgnorePrefix;

        return $this;
    }

    /**
     * @return array
     */
    public function getIncludedFields()
    {
        return $this->includedFields;
    }

    /**
     * @param array $includedFields
     * @return DqbSearchItem
     */
    public function setIncludedFields($includedFields)
    {
        $this->includedFields = $includedFields;

        return $this;
    }

    /**
     * @return array
     */
    public function getExcludedFields()
    {
        return $this->excludedFields;
    }

    /**
     * @param array $excludedFields
     * @return DqbSearchItem
     */
    public function setExcludedFields($excludedFields)
    {
        $this->excludedFields = $excludedFields;

        return $this;
    }
}