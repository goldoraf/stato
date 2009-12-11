<?php

namespace Stato\Orm;

use ReflectionClass, ReflectionObject;

require_once 'Schema.php';
require_once 'Properties.php';

class Mapper
{
    private static $defaultOptions = array();
    
    private $className;
    private $table;
    private $identifier;
    private $properties;
    private $connection;
    private $hydrator;
    private $dessicator;
    
    public function __construct($className, Table $table, Connection $conn, array $options = array())
    {
        $this->className = $className;
        $this->table = $table;
        $this->connection = $conn;
        $this->identifier = $this->table->getPrimaryKey();
        $this->hydrator = null;
        $this->dessicator = null;
        $this->properties = array();
        
        if (!array_key_exists('properties', $options)) {
            $properties = $this->table->getColumns();
        } else {
            $properties = $options['properties'];
        }
        
        if (array_key_exists('include_properties', $options)) {
            foreach ($properties as $k => $v)
                if (!in_array($k, $options['include_properties'])) unset($properties[$k]);
        } elseif (array_key_exists('exclude_properties', $options)) {
            foreach ($options['exclude_properties'] as $exc) unset($properties[$exc]);
        }
        
        $ref = new ReflectionClass($this->className);
        foreach ($properties as $propName => $prop) {
            if (!$prop instanceof ClauseColumn) {
                $prop = $this->table->getCorrespondingColumn($prop);
            }
            $this->properties[$propName] = $prop;
        }
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
    
    public function getHydrator()
    {
        if (is_null($this->hydrator)) {
            $this->hydrator = new ObjectHydrator($this->className);
            foreach ($this->properties as $propertyName => $column) {
                $this->hydrator->addProcessor($column->name, $propertyName, $this->connection->getDialect()->getTypecastingClass($column->type)->getResultProcessor());
            }
        }
        return $this->hydrator;
    }
    
    public function getDessicator()
    {
        if (is_null($this->dessicator)) {
            $this->dessicator = new ObjectDessicator($this->className);
            foreach ($this->properties as $propertyName => $column) {
                $this->dessicator->addProcessor($propertyName, $column->name, $this->connection->getDialect()->getTypecastingClass($column->type)->getBindProcessor());
            }
        }
        return $this->dessicator;
    }
    
    public function insertObject($object)
    {
        $dessicator = $this->getDessicator();
        $insert = new Insert($this->table, $dessicator->dessicate($object));
        $res = $this->connection->execute($insert);
        $id = $res->lastInsertId();
        $object = $this->getHydrator()->hydrate($object, array($this->identifier => $id));
        return $id;
    }
    
    public function updateObject($object)
    {
        $id = $object->{$this->identifier};
        $dessicator = $this->getDessicator();
        $update = new Update($this->table, $dessicator->dessicate($object), $this->table->{$this->identifier}->eq($id));
        return $this->connection->execute($update);
    }
    
    public function deleteObject($object)
    {
        $id = $object->{$this->identifier};
        $delete = new Delete($this->table, $this->table->{$this->identifier}->eq($id));
        return $this->connection->execute($delete);
    }
}

class ObjectHydrator
{
    private $reflector;
    private $processors;
    private $columnToProperties;
    
    public function __construct($className)
    {
        $this->reflector = new ReflectionClass($className);
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
            if (!$this->reflector->hasProperty($propertyName)) {
                $object->{$propertyName} = $value;
            } else {
                $property = $this->reflector->getProperty($propertyName);
                $property->setAccessible(true);
                $property->setValue($object, $value);
            }
        }
        $object->setAsLoaded();
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
    private $processors;
    private $propertyToColumns;
    
    public function __construct($className)
    {
        $this->processors = array();
        $this->propertyToColumns = array();
    }
    
    public function dessicate($object)
    {
        $values = array();
        $ref = new \ReflectionObject($object);
        foreach ($this->propertyToColumns as $propertyName => $columnName) {
            if (!$ref->hasProperty($propertyName)) continue;
            $property = $ref->getProperty($propertyName);
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