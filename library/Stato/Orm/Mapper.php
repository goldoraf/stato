<?php

namespace Stato\Orm;

require_once 'Schema.php';
require_once 'Properties.php';

class Mapper
{
    private static $defaultOptions = array();
    
    private $className;
    private $table;
    private $identifier;
    private $properties;
    private $hydrator;
    private $dessicator;
    
    public function __construct($className, Table $table, array $options = array())
    {
        $this->className = $className;
        $this->table = $table;
        $this->identifier = $this->table->getPrimaryKey();
        $this->hydrator = null;
        $this->dessicator = null;
        
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
        return $this->table->getName();
    }
    
    public function getClassName()
    {
        return $this->className;
    }
    
    public function getIdentifier()
    {
        return $this->identifier;
    }
    
    public function getHydrator(Dialect\IDialect $dialect)
    {
        if (is_null($this->hydrator)) {
            $this->hydrator = new ObjectHydrator($this->className);
            foreach ($this->properties as $propertyName => $column) {
                $this->hydrator->addProcessor($column->name, $propertyName, $dialect->getTypecastingClass($column->type)->getResultProcessor());
            }
        }
        return $this->hydrator;
    }
    
    public function getDessicator(Dialect\IDialect $dialect)
    {
        if (is_null($this->dessicator)) {
            $this->dessicator = new ObjectDessicator($this->className);
            foreach ($this->properties as $propertyName => $column) {
                $this->dessicator->addProcessor($propertyName, $column->name, $dialect->getTypecastingClass($column->type)->getBindProcessor());
            }
        }
        return $this->dessicator;
    }
    
    public function insertObject($object, Connection $conn)
    {
        $dessicator = $this->getDessicator($conn->getDialect());
        $insert = new Insert($this->table, $dessicator->dessicate($object));
        $res = $conn->execute($insert);
        $id = $res->lastInsertId();
        $object = $this->getHydrator($conn->getDialect())->hydrate($object, array($this->identifier => $id));
        return $id;
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
    
    public function newInstance(array $values, Session $session = null)
    {
        if (!is_null($session))
            $instance = $session->newInstance($this->reflector->getName(), $values);
        else
            $instance = $this->reflector->newInstance();
        
        return $this->hydrate($instance, $values);
    }
    
    public function hydrate($object, array $values)
    {
        foreach ($values as $columnName => $value) {
            if (!isset($this->columnToProperties[$columnName])) continue;
            $propertyName = $this->columnToProperties[$columnName];
            $value = call_user_func($this->processors[$propertyName], $value);
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

class ObjectDessicator
{
    private $reflector;
    private $processors;
    private $propertyToColumns;
    
    public function __construct($className)
    {
        $this->reflector = new \ReflectionClass($className);
        $this->processors = array();
        $this->propertyToColumns = array();
    }
    
    public function dessicate($object)
    {
        $values = array();
        foreach ($this->propertyToColumns as $propertyName => $columnName) {
            $property = $this->reflector->getProperty($propertyName);
            $property->setAccessible(true);
            $value = call_user_func($this->processors[$propertyName], $property->getValue($object));
            $values[$columnName] = $value;
        }
        return $values;
    }
    
    public function addProcessor($propertyName, $columnName, $processor)
    {
        $this->propertyToColumns[$propertyName] = $columnName;
        $this->processors[$propertyName] = $processor;
    }
}