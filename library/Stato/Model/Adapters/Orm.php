<?php

namespace Stato\Model\Adapters;

use Stato\Model\Query;
use Stato\Orm\Connection;
use Stato\Orm\Insert;
use Stato\Orm\Select;

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
        $changed = $object->getChangedProperties();
        $bindValues = array();
        foreach ($changed as $property) {
            if ($property == $serial) {
                $serial = false;
            }
            $bindValues[$property] = $object->__get($property);
        }
        
        $insert = new Insert($this->getCorrespondingTable(get_class($object)), $bindValues);
        $result = $this->connection->execute($insert);
        
        if ($result->affectedRows() === 1 && $serial) {
            $object->{$serial} = $result->lastInsertId();
        }
    }
    
    public function read(Query $query)
    {
        $meta   = $query->getMetaclass();
        $table  = $this->getCorrespondingTable($meta->getModelName());
        $select = new Select(array($table));
        
        $conditions = array();
        foreach ($query->getConditions() as $condition) {
            $columnName = isset($condition->subject->column) 
                        ? $condition->subject->column : $condition->subject->name;
            $conditions[] = $table->{$columnName}->op($condition->operator, $condition->value);
        }
        call_user_func_array(array($select, 'where'), $conditions);
        
        $result = $this->connection->execute($select);
        return $result->fetchAll();
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
    
    private function getTable($tableName)
    {
        
    }
}