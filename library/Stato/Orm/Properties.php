<?php

namespace Stato\Orm;

class ColumnProperty
{
    
}

class RelationProperty
{
    private static $defaultOptions = array('primary_join' => null, 'collection' => false);
    
    private $parent;
    private $mapper;
    private $parentMapper;
    private $primaryJoin;
    private $collection;
    
    public function __construct($mapper, $parentMapper, $primaryJoin = null, $collection = false)
    {
        $this->mapper = $mapper;
        $this->parentMapper = $parentMapper;
        $this->primaryJoin = $primaryJoin;
        $this->collection = $collection;
        
        $this->determineJoins();
    }
    
    public function assign($parent)
    {
        $this->parent = $parent;
    }
    
    private function determineJoins()
    {
        if (is_null($this->primaryJoin)) {
            $this->primaryJoin = new Join($this->mapper->table, $this->parentMapper->table);
        }
    }
}