<?php

namespace Stato\Orm;

require_once 'Schema.php';
require_once 'Properties.php';

class Mapper
{
    private static $instances = array();
    private static $defaultRelationOptions = array('primary_join' => null, 'collection' => false);
    
    public $entity;
    public $table;
    public $columns;
    public $properties;
    
    public static function getClassMapper($className)
    {
        if (!array_key_exists($className, self::$instances)) {
            $ref = new \ReflectionClass($className);
            if ($ref->isSubclassOf(__NAMESPACE__ . '\Entity')) {
                self::$instances[$className] = new EntityMapper($className);
            } else {
                throw new Exception("No mapper available for {$className} class");
            }
        }
            
        return self::$instances[$className];
    }
    
    public function __construct($entity, Table $table, array $relations = array())
    {
        $this->entity = $entity;
        $ref = new \ReflectionClass($this->entity);
        $this->table = $table;
        $this->columns = $this->table->getColumns();
        
        $this->properties = array();
        foreach ($relations as $name => $relation) {
            $this->properties[$name] 
                = new PropertyLoader($relation->mapper, $this, $relation->options['primary_join'],
                    $relation->options['collection']);
        }
        
        self::$instances[$this->entity] = $this;
    }
    
    public function populate($object, $values)
    {
        foreach ($this->columns as $name => $column) {
            if (array_key_exists($name, $values))
                $object->$name = $values[$name];
            else
                $object->$name = (!$column->default) ? null : $column->default;
        }
    }
    
    public function setFetchMode(ResultProxy $res)
    {
        $res->setFetchMode(Connection::FETCH_OBJECT, $this->entity);
    }
    
    public function getProperty($propertyName)
    {
        return false;
    }
}
