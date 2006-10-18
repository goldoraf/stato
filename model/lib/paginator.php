<?php

class SPaginator
{
    public $perPage     = null;
    public $currentPage = null;
    
    private $querySet  = null;
    private $param     = null;
    private $pageCount = null;
    
    public function __construct($querySet, $perPage=20, $currentPage=1, $param='page')
    {
        $this->querySet    = $querySet;
        $this->perPage     = $perPage;
        $this->currentPage = $currentPage;
        $this->param       = $param;
    }
    
    public function currentPage()
    {
        return $this->getPage($this->currentPage);
    }
    
    public function getPage($page)
    {
        return $this->querySet->limit($this->perPage, ($page - 1) * $this->perPage);
    }
    
    public function hasNextPage($page)
    {
        return $page < $this->pageCount() && $page > 0;
    }
    
    public function hasPreviousPage($page)
    {
        return $page > 1;
    }
    
    public function windowPages($windowSize)
    {
        $window = array();
        $first = $this->currentPage - $windowSize;
        $last  = $this->currentPage + $windowSize;
        for ($i=1; $i<=$this->pageCount(); $i++)
        {
            if ($i >= $first && $i <= $last) $window[] = $i;
        }
        return $window;
    }
    
    public function pageCount()
    {
        if ($this->pageCount == Null) $this->pageCount = ceil($this->hitsCount() / $this->perPage);
        return $this->pageCount;
    }
    
    public function hitsCount()
    {
        return $this->querySet->count();
    }
}

?>
