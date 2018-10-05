<?php

namespace Strongknit\DqbsBundle;

class SearchItem
{
    protected $entityAlias;

    protected $searchValue;

    protected $searchValueIgnorePrefix;

    protected $searchByEntityFields;

    protected $includedFields;

    protected $excludedFields;

    public function __construct($entityAlias, $searchValue)
    {
        $this->entityAlias = $entityAlias;
        $this->searchValue = $searchValue;
        $this->searchByEntityFields = true;
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
     * @return SearchItem
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
     * @return SearchItem
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
     * @return SearchItem
     */
    public function setSearchValueIgnorePrefix($searchValueIgnorePrefix)
    {
        $this->searchValueIgnorePrefix = $searchValueIgnorePrefix;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSearchByEntityFields()
    {
        return $this->searchByEntityFields;
    }

    /**
     * @param bool $searchByEntityFields
     * @return SearchItem
     */
    public function setSearchByEntityFields($searchByEntityFields)
    {
        $this->searchByEntityFields = $searchByEntityFields;

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
     * @param mixed $includedFields
     * @return SearchItem
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
     * @return SearchItem
     */
    public function setExcludedFields($excludedFields)
    {
        $this->excludedFields = $excludedFields;

        return $this;
    }
}