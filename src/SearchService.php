<?php

namespace Strongknit\DqbsBundle;

class SearchService
{
    protected $searchItems;

    public function __construct()
    {
        $this->searchItems = [];
    }

    public function addSearchItem(SearchItem $searchItem)
    {
        $this->searchItems[] = $searchItem;
    }

    public function applySearch()
    {

    }
}
