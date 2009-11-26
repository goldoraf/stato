<?php

namespace Stato\Orm;

require_once 'Schema.php';
require_once 'Properties.php';

class Mapper
{
    private static $defaultOptions = array();
    
    private $className;
    private $tableName;
    private $columns;
    private $properties;
    private $hydrator;
    
    public function __construct($className, Table $table, array $options = array())
    {
        $this->className = $className;
        $this->tableName = $table->getName();
        $this->hydrator = null;
        
        $this->properties = array();
        $ref = new \ReflectionClass($this->className);
        $props = $ref->getProperties();
        foreach ($props as $refProp) {
            if (($column = $table->getCorrespondingColumn($refProp->getName())) !== false) {
                $this->properties[$refProp->getName()] = $column;
            }
        }
        /*foreach ($relations as $name => $relation) {
            $this->properties[$name] 
                = new PropertyLoader($relation->mapper, $this, $relation->options['primary_join'],
                    $relation->options['collection']);
        }
        
        self::$instances[$this->entity] = $this;*/
    }
    
    public function getTableName()
    {
        return $this->tableName;
    }
    
    public function getHydrator(Dialect\IDialect $dialect)
    {
        if (is_null($this->hydrator)) {
            $this->hydrator = new ObjectHydrator($this->className);
            foreach ($this->properties as $propertyName => $column) {
                $this->hydrator->addProcessor($column->name, $propertyName, $dialect->getResultProcessor($column->type));
            }
        }
        return $this->hydrator;
    }
}

class ObjectHydrator
{
    private $reflector;
    private $processors;
    private $columnToProperties;
    
    public function __construct($className)
    {
        $this->reflector = new \ReflectionClass($className);
        $this->processors = array();
        $this->columnToProperties = array();
    }
    
    public function newInstance(array $values)
    {
        return $this->hydrate($this->reflector->newInstance(), $values);
    }
    
    public function hydrate($object, array $values)
    {
        foreach ($this->columnToProperties as $columnName => $propertyName) {
            $value = call_user_func($this->processors[$propertyName], $values[$columnName]);
            $property = $this->reflector->getProperty($propertyName);
            $property->setAccessible(true);
            $property->setValue($object, $value);
        }
        return $object;
    }
    
    public function addProcessor($columnName, $propertyName, $processor)
    {
        $this->columnToProperties[$columnName] = $propertyName;
        $this->processors[$propertyName] = $processor;
    }
}