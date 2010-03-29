<?php

namespace Stato\Model;

use \Exception;

class Query
{
    private $metaclass;
    
    private $conditions;
    
    private $offset;
    
    private $limit;
    
    public function __construct(Metaclass $meta)
    {
        $this->metaclass = $meta;
        $this->conditions = array();
        $this->offset = 0;
        $this->limit = null;
    }
    
    public function update(array $conditions)
    {
        $this->conditions = array_merge($this->conditions, $conditions);
        return $this;
    }
    
    public function limit($limit, $offset = 0)
    {
        $this->limit = $limit;
        $this->offset = $offset;
        return $this;
    }
    
    public function getMetaclass()
    {
        return $this->metaclass;
    }
    
    public function getConditions()
    {
        return $this->conditions;
    }
}