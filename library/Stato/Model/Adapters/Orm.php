<?php

namespace Stato\Model\Adapters;

use Stato\Orm\Connection;
use Stato\Orm\Insert;

class Orm
{
    private $connection;
    
    private $tables = array();
    
    public function __construct($config)
    {
        $this->connection = new Connection($config);
    }
    
    public function create($object)
    {
        $meta = $object->getMetaclass();
        $serial = $meta->getSerial();
        $values = $object->getChangedValues();
        $bindValues = array();
        
        foreach ($meta->getProperties() as $property) {
            if (!array_key_exists($property->name, $values)) continue;
            
            // serial property has been set manually, don't change it
            if ($property->name == $serial) {
                $serial = false;
            }
            
            $bindValues[$property->name] = $values[$property->name];
        }
        
        $insert = new Insert($this->getCorrespondingTable(get_class($object)), $bindValues);
        $result = $this->connection->execute($insert);
        
        if ($result->affectedRows() === 1 && $serial) {
            $object->{$serial} = $result->lastInsertId();
        }
    }
    
    public function getStorageNamingConvention()
    {
        return function($className) {
            return strtolower($className).'s';
        };
    }
    
    public function getCorrespondingTable($class)
    {
        if (!array_key_exists($class, $this->tables)) {
            $namingConvention = $this->getStorageNamingConvention();
            $this->tables[$class] = $this->connection->reflectTable($namingConvention($class));
        }
        return $this->tables[$class];
    }
}