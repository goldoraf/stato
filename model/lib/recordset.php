<?php

class SRecordset
{
    protected $resource;
    private $class;
    
    public function __construct($resource, $class)
    {
        $this->resource = $resource;
        $this->class = $class;
    }
    
    public function fetch()
    {
        return call_user_func(array($this->class, 'fetch'), $this->resource);
    }
    
    public function rowCount()
    {
        return call_user_func(array($this->class, 'rowCount'), $this->resource);
    }
}

?>
