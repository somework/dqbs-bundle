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
     * @param array  $includeFields
     * @param array  $excludedFields
     */
    public function __construct(
        string $entityAlias,
        string $searchValue = '',
        array $includeFields = [],
        array $excludedFields = []
    ) {
        $this->entityAlias = $entityAlias;
        $this->searchValue = $searchValue;
        $this->searchByEntityFields = true;
        $this->includedFields = $includeFields;
        $this->excludedFields = $excludedFields;
    }

    /**
     * @return string
     */
    public function getEntityAlias(): string
    {
        return $this->entityAlias;
    }

    /**
     * @param string $entityAlias
     * @return DqbSearchItem
     */
    public function setEntityAlias(string $entityAlias): DqbSearchItem
    {
        $this->entityAlias = $entityAlias;

        return $this;
    }

    /**
     * @return string
     */
    public function getSearchValue(): string
    {
        return $this->searchValue;
    }

    /**
     * @param string $searchValue
     * @return DqbSearchItem
     */
    public function setSearchValue(string $searchValue): DqbSearchItem
    {
        $this->searchValue = $searchValue;

        return $this;
    }

    /**
     * @return string
     */
    public function getSearchValueIgnorePrefix(): string
    {
        return $this->searchValueIgnorePrefix;
    }

    /**
     * @param string $searchValueIgnorePrefix
     * @return DqbSearchItem
     */
    public function setSearchValueIgnorePrefix(string $searchValueIgnorePrefix): DqbSearchItem
    {
        $this->searchValueIgnorePrefix = $searchValueIgnorePrefix;

        return $this;
    }

    /**
     * @return array
     */
    public function getIncludedFields(): array
    {
        return $this->includedFields;
    }

    /**
     * @param array $includedFields
     * @return DqbSearchItem
     */
    public function setIncludedFields(array $includedFields): DqbSearchItem
    {
        $this->includedFields = $includedFields;

        return $this;
    }

    /**
     * @return array
     */
    public function getExcludedFields(): array
    {
        return $this->excludedFields;
    }

    /**
     * @param array $excludedFields
     * @return DqbSearchItem
     */
    public function setExcludedFields(array $excludedFields): DqbSearchItem
    {
        $this->excludedFields = $excludedFields;

        return $this;
    }
}