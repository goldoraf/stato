<?php

class SPaginator
{
    public $per_page     = null;
    public $current_page = null;
    
    private $query_set  = null;
    private $param     = null;
    private $page_count = null;
    
    public function __construct($query_set, $per_page=20, $current_page=1, $param='page')
    {
        $this->query_set    = $query_set;
        $this->per_page     = $per_page;
        $this->current_page = $current_page;
        $this->param       = $param;
    }
    
    public function current_page()
    {
        return $this->get_page($this->current_page);
    }
    
    public function get_page($page)
    {
        return $this->query_set->limit($this->per_page, ($page - 1) * $this->per_page);
    }
    
    public function has_next_page($page)
    {
        return $page < $this->page_count() && $page > 0;
    }
    
    public function has_previous_page($page)
    {
        return $page > 1;
    }
    
    public function window_pages($window_size)
    {
        $window = array();
        $first = $this->current_page - $window_size;
        $last  = $this->current_page + $window_size;
        for ($i=1; $i<=$this->page_count(); $i++)
        {
            if ($i >= $first && $i <= $last) $window[] = $i;
        }
        return $window;
    }
    
    public function page_count()
    {
        if ($this->page_count == Null) $this->page_count = ceil($this->hits_count() / $this->per_page);
        return $this->page_count;
    }
    
    public function hits_count()
    {
        return $this->query_set->count();
    }
}

?>
