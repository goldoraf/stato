<?php

namespace Stato\Model;

use \Exception, \ReflectionObject;
class MethodMissingException extends Exception {}
class PropertyMissingException extends Exception {}

class Metaclass
{
    const SERIAL    = 'serial';
    const INTEGER   = 'integer';
    const STRING    = 'string';
    const BOOLEAN   = 'boolean';
    const DATE      = 'date';
    const DATETIME  = 'datetime';
    const TIMESTAMP = 'timestamp';
    const FLOAT     = 'float';
    const TEXT      = 'text';
    
    protected $model;
    
    protected $repositoryName;
    
    protected $storageName = null;
    
    protected $properties = array();
    
    private $methods = array();
    
    private $serial = false;
    
    private $key = array();
    
    private $columnMap = array();
    
    public function __get($propertyName)
    {
        return $this->getProperty($propertyName);
    }
    
    public function __set($propertyName, Property $property)
    {
        $this->appendProperty($property);
    }
    
    public function addProperty($name, $type = self::STRING, array $options = array())
    {
        $this->appendProperty(new Property($name, $type, $options));
    }
    
    public function getProperty($name)
    {
        if (!array_key_exists($name, $this->properties)) {
            throw new PropertyMissingException("Undefined property '$propertyName'");
        }
        return $this->properties[$name];
    }
    
    public function setModelClass($className)
    {
        $this->model = $className;
    }
    
    public function getModelClass()
    {
        return $this->model;
    }
    
    public function setStorageName($storageName)
    {
        $this->storageName = $storageName;
    }
    
    public function getStorageName()
    {
        return $this->storageName;
    }
    
    public function getProperties(array $propertyNames = null)
    {
        if (!is_null($propertyNames)) {
            return array_intersect_key($this->properties, array_flip($propertyNames));
        }
        return $this->properties;
    }
    
    public function getSerial()
    {
        return $this->serial;
    }
    
    public function getKey()
    {
        return $this->key;
    }
    
    public function load($records)
    {
        $objects = array();
        foreach ($records as $record) {
            $object = $this->instantiate();
            foreach ($record as $column => $value) {
                if (!array_key_exists($column, $this->columnMap)) continue;
                $property = $this->getPropertyFromColumnName($column);
                $property->set($object, $value);
            }
            if ($object instanceof Base) {
                $object->setAsSaved();
            }
            $objects[] = $object;
        }
        return $objects;
    }
    
    public function instantiate()
    {
        $class = $this->model;
        return new $class;
    }
    
    public function defineDynamicMethods($methodTarget, $prefix, $suffix = '', array $properties = null)
    {
        if (is_null($properties)) {
            $properties = array_keys($this->properties);
        } else {
            $this->checkPropertiesExistence($properties);
        }
        foreach ($properties as $property) {
            $methodName = $prefix.ucfirst($property).$suffix;
            $this->methods[$methodName] = array($property, $methodTarget);
        }
    }
    
    public function getMethodMissingTarget($methodMissing, $callerClass)
    {
        if (!array_key_exists($methodMissing, $this->methods)) {
            throw new MethodMissingException("Call to undefined method $callerClass::$methodMissing()");
        }
        return $this->methods[$methodMissing];
    }
    
    public function checkPropertiesExistence($properties)
    {
        $missingProperties = array_diff($properties, array_keys($this->properties));
        if (!empty($missingProperties)) {
            throw new PropertyMissingException("Properties not defined: ".implode($missingProperties));
        }
    }
    
    public function hasProperty($name)
    {
        return array_key_exists($name, $this->properties);
    }
    
    private function getPropertyFromColumnName($columnName)
    {
        if (!array_key_exists($columnName, $this->columnMap)) {
            throw new Exception("No property found for column '$columnName'");
        }
        $propertyName = $this->columnMap[$columnName];
        return $this->getProperty($propertyName);
    }
    
    private function appendProperty(Property $property)
    {
        if (array_key_exists($property->name, $this->properties)) {
            throw new Exception("Already defined property '{$property->name}'");
        }
        
        if ($property->type == self::SERIAL) $this->serial = $property->name;
        if ($property->key) $this->key[] = $property;
        
        $this->properties[$property->name] = $property;
        $this->columnMap[$property->column] = $property->name;
    }
}