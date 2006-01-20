<?php

class Paginator
{
    public $perPage     = 20;
    public $currentPage = 1;
    
    private $entity    = Null;
    private $condition = Null;
    private $options   = array();
    private $param     = 'page';
    private $pageCount = Null;
    
    public function __construct($entityClass, $perPage=20, $options=array())
    {
        $this->entity  = $entityClass;
        $this->perPage = $perPage;
        $this->options = $options;
        if (isset($options['parameter']))  $this->param = $options['parameter'];
        if (isset($options['conditions'])) $this->condition = $options['conditions'];
        
        if (isset(Context::$request->params[$this->param]))
            $this->currentPage = Context::$request->params[$this->param];
    }
    
    public function currentPage()
    {
        return $this->getPage($this->currentPage);
    }
    
    public function getPage($page)
    {
        return ActiveStore::findAll($this->entity, $this->condition, $this->sqlOptions($page));
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
        return ActiveStore::count($this->entity, $this->condition);
    }
    
    private function sqlOptions($page)
    {
        $offset = ($page - 1) * $this->perPage;
        return array_merge($this->options, array('offset' => $offset, 'limit' => $this->perPage));
    }
}

?>
