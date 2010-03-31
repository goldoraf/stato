<?php

namespace Stato\Model;

use Exception;
use ReflectionClass;

class RepositoryNotSetupException extends Exception {}

class Repository
{
    private static $instances = array();
    
    public static function setup($repositoryName, $config)
    {
        self::$instances[$repositoryName] = new Repository($repositoryName, self::instantiateAdapter($config));
    }
    
    public static function get($name = null)
    {
        if (is_null($name)) {
            $name = self::getDefaultName();
        }
        if (!array_key_exists($name, self::$instances)) {
            throw new RepositoryNotSetupException($name);
        }
        return self::$instances[$name];
    }
    
    public static function getDefaultName()
    {
        return 'default';
    }
    
    private static function instantiateAdapter($config)
    {
        if (!array_key_exists('driver', $config)) {
            throw new Exception("Please provide a 'driver' option for repository setup");
        }
        $driverName = strtolower($config['driver']);
        if (in_array($driverName, array('mysql'))) {
            $adapterClass = __NAMESPACE__ . '\Adapters\Orm';
        } else {
            throw new Exception("No adapter found for '$driverName' driver");
        }
        return new $adapterClass($config);
    }
    
    private $name;
    
    private $adapter;
    
    private $metaclasses;
    
    private $collections;
    
    public function __construct($name, $adapter)
    {
        $this->name = $name;
        $this->adapter = $adapter;
        $this->metaclasses = array();
        $this->collections = array();
    }
    
    public function getAdapter()
    {
        return $this->adapter;
    }
    
    public function addMetaclass(Metaclass $metaclass)
    {
        $modelClass  = $metaclass->getModelClass();
        $storageName = $metaclass->getStorageName();
        if (is_null($storageName)) {
            $namingConvention = $this->adapter->getStorageNamingConvention();
            $storageName = $namingConvention($modelClass);
            $metaclass->setStorageName($storageName);
        }
        $this->metaclasses[$modelClass]  = $metaclass;
        $this->collections[$storageName] = $modelClass;
    }
    
    public function getMetaclass($className)
    {
        if (!array_key_exists($className, $this->metaclasses)) {
            throw new Exception("No metaclass defined for class '$className'");
        }
        return $this->metaclasses[$className];
    }
    
    public function getModelClass($collectionName)
    {
        if (!array_key_exists($collectionName, $this->collections)) {
            throw new Exception("Unknown collection '$collectionName'");
        }
        return $this->collections[$collectionName];
    }
    
    public function map($modelClass, $collectionName, array $options = array())
    {
        $this->addMetaclass($this->constructMetaclass($modelClass, $collectionName, $options));
    }
    
    public function from($collectionName)
    {
        return new Dataset($this->getMetaclass($this->getModelClass($collectionName)), $this);
    }
    
    public function create($object)
    {
        return $this->getAdapter()->create($this->getMetaclass(get_class($object)), $object);
    }
    
    public function read(Query $query)
    {
        return $query->getMetaclass()->load($this->getAdapter()->read($query));
    }
    
    private function constructMetaclass($modelClass, $collectionName, array $options)
    {
        $metaclass = new Metaclass();
        $metaclass->setModelClass($modelClass);
        $metaclass->setStorageName($collectionName);
        
        if (array_key_exists('properties', $options)) {
            $properties = $options['properties'];
        } else {
            if (!$this->adapter->supportsReflection()) {
                throw new Exception("This adapter does not support reflection ; you must provide a properties array");
            }
            $properties = $this->adapter->reflect($collectionName);
        }
        
        $include = array_key_exists('include_properties', $options) ? $options['include_properties'] : false;
        $exclude = array_key_exists('exclude_properties', $options) ? $options['exclude_properties'] : array();
        
        $ref = new ReflectionClass($modelClass);
        foreach ($properties as $property) {
            if (!$ref->hasProperty($property->name)) {
                throw new Exception("Property '{$property->name}' not found in $modelClass ; mapping fails");
            }
            if (($include === false || in_array($property->name, $include)) && !in_array($property->name, $exclude)) {
                $metaclass->{$property->name} = $property;
            }
        }
        return $metaclass;
    }
}