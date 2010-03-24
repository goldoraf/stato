<?php

namespace Stato\Model\Adapters;

use Stato\Orm\Connection;
use Stato\Orm\Insert;

class Orm
{
    private $connection;
    
    public function __construct($config)
    {
        $this->connection = new Connection($config);
    }
    
    public function create($object)
    {
        $values = $object->getChangedValues();
        $bindValues = array();
        
        foreach ($object->getMetadata()->getProperties() as $property => $options) {
            if (!array_key_exists($property, $values)) continue;
            
            $bindValues[$property] = $values[$property];
        }
        
        $namingConvention = $this->getStorageNamingConvention();
        $table = $this->connection->reflectTable($namingConvention(get_class($object)));
        
        $insert = new Insert($table, $bindValues);
        $res = $this->connection->execute($insert);
        $id = $res->lastInsertId();
    }
    
    public function getStorageNamingConvention()
    {
        return function($className) {
            return strtolower($className).'s';
        };
    }
}