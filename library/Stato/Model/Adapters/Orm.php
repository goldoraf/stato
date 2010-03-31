<?php

namespace Stato\Model\Adapters;

use Stato\Model\Query;
use Stato\Model\Property;
use Stato\Model\Metaclass;
use Stato\Model\Base;
use Stato\Orm\Connection;
use Stato\Orm\Insert;
use Stato\Orm\Select;

use \Closure;
use \ReflectionObject;

class Orm
{
    private $connection;
    
    private $storageNamingConvention;
    
    private $tables = array();
    
    public function __construct($config)
    {
        $this->connection = new Connection($config);
        $this->storageNamingConvention = function($className) {
            return strtolower($className).'s';
        };
    }
    
    public function supportsReflection()
    {
        return true;
    }
    
    public function setStorageNamingConvention(Closure $convention)
    {
        $this->storageNamingConvention = $convention;
    }
    
    public function getStorageNamingConvention()
    {
        return $this->storageNamingConvention;
    }
    
    public function create($metaclass, $object)
    {
        $values = array();
        
        if ($object instanceof Base) {
            $properties = $metaclass->getProperties($object->getChangedProperties());
        } else {
            $properties = $metaclass->getProperties();
        }
        
        foreach ($properties as $property) {
            $values[$property->column] = $property->get($object);
        }
        
        $insert = new Insert($this->getTable($metaclass->getStorageName()), $values);
        $result = $this->connection->execute($insert);
        
        if ($result->affectedRows() === 1 && $metaclass->getSerial() !== false) {
            $serialProperty = $metaclass->getProperty($metaclass->getSerial());
            $serialProperty->set($object, $result->lastInsertId());
        }
    }
    
    public function read(Query $query)
    {
        $metaclass = $query->getMetaclass();
        $table = $this->getTable($metaclass->getStorageName());
        $select = new Select(array($table));
        
        $conditions = array();
        foreach ($query->getConditions() as $condition) {
            $column = $this->getColumnFromProperty($table, $condition->subject);
            $newCondition = $column->op($condition->operator, $condition->value);
            if ($condition->negated) {
                $newCondition->negate();
            }
            $conditions[] = $newCondition;
        }
        if (count($conditions) !== 0) {
            call_user_func_array(array($select, 'where'), $conditions);
        }
        
        $sorts = array();
        foreach ($query->getSorts() as $sort) {
            $column = $this->getColumnFromProperty($table, $sort->subject);
            $newSort = ($sort->ascending) ? $column->asc() : $column->desc();
            $sorts[] = $newSort;
        }
        if (count($sorts) !== 0) {
            call_user_func_array(array($select, 'orderBy'), $sorts);
        }
        
        $limit = $query->getLimit();
        if ($limit) {
            $select->limit($limit);
        }
        $offset = $query->getOffset();
        if ($offset) {
            $select->offset($offset);
        }
        
        return $this->connection->execute($select);
    }
    
    public function reflect($storageName)
    {
        $table = $this->getTable($storageName);
        $properties = array();
        foreach ($table->getColumns() as $column) {
            $type = ($column->autoIncrement) ? Metaclass::SERIAL : $column->type;
            $options = array(
                'key'      => $column->primaryKey,
                'nullable' => $column->nullable,
                'length'   => $column->length,
                'index'    => $column->index,
                'unique'   => $column->unique
            );
            if (isset($column->default)) $options['default'] = $column->default;
            $properties[] = new Property($column->name, $type, $options);
        }
        return $properties;
    }
    
    private function getTable($tableName)
    {
        if (!array_key_exists($tableName, $this->tables)) {
            $this->tables[$tableName] = $this->connection->reflectTable($tableName);
        }
        return $this->tables[$tableName];
    }
    
    private function getColumnFromProperty($table, $property)
    {
        $columnName = $property->column;
        return $table->{$columnName};
    }
}