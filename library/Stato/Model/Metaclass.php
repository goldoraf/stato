<?php

namespace Stato\Model;

use \Exception;
class MethodMissingException extends Exception {}
class PropertyMissingException extends Exception {}

class Metaclass
{
    const SERIAL = 'serial';
    
    const STRING = 'string';
    
    const TEXT = 'text';
    
    const DATETIME = 'datetime';
    
    const INTEGER = 'integer';
    
    const FLOAT = 'float';
    
    private $model;
    
    private $properties = array();
    
    private $methods = array();
    
    private $serial = false;
    
    private $key = array();
    
    private $columnMap;
    
    public function __get($propertyName)
    {
        if (!array_key_exists($propertyName, $this->properties)) {
            throw new PropertyMissingException("Undefined property '$propertyName'");
        }
        return $this->properties[$propertyName];
    }
    
    public function addProperty($name, $type = self::STRING, array $options = array())
    {
        $property = new Property($name, $type, $options);
        if ($property->type == self::SERIAL) $this->serial = $name;
        if ($property->key) $this->key[] = $property;
        
        $this->properties[$name] = $property;
        $this->columnMap[isset($property->column) ? $property->column : $property->name] = $property->name;
    }
    
    public function setModelName($name)
    {
        $this->model = $name;
    }
    
    public function getModelName()
    {
        return $this->model;
    }
    
    public function getProperties()
    {
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
        $models = array();
        foreach ($records as $record) {
            $model = $this->instantiate();
            foreach ($record as $column => $value) {
                $model->__set($this->columnMap[$column], $value); // TODO : typecasting
            }
            $model->setAsSaved();
            $models[] = $model;
        }
        return $models;
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
}