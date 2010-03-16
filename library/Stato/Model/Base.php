<?php

namespace Stato\Model;

class MethodMissingTargetException extends Exception {}

class Base
{
    protected static $metadata;
    
    static public function setMetadata(Metadata $meta)
    {
        $meta->defineDynamicMethods('getProperty', 'get');
        $meta->defineDynamicMethods('setProperty', 'set');
        self::$metadata[get_called_class()] = $meta;
    }
    
    static public function getMetadata()
    {
        $class = get_called_class();
        if (!isset(self::$metadata[$class])) {
            throw new Exception("No metadata class assigned to class $class");
        }
        return self::$metadata[$class];
    }
    
    protected $values = array();
    
    public function __construct(array $values = null)
    {
        if (!is_null($values)) {
            static::getMetadata()->checkPropertiesExistence(array_keys($values));
            $this->values = $values;
        }
    }
    
    public function __get($property)
    {
        $possibleGetter = 'get'.ucfirst($property);
        if (method_exists($this, $possibleGetter)) {
            return $this->$possibleGetter();
        }
        return $this->getProperty($property);
    }
    
    public function __set($property, $value)
    {
        $possibleSetter = 'set'.ucfirst($property);
        if (method_exists($this, $possibleSetter)) {
            return $this->$possibleSetter($value);
        }
        return $this->setProperty($property, $value);
    }
    
    public function __call($method, $args)
    {
        list($propertyName, $methodTarget) 
            = static::getMetadata()->getMethodMissingTarget($method, get_called_class());
        if (!method_exists($this, $methodTarget)) {
            throw new MethodMissingTargetException("Call to undefined method target '$methodTarget'");
        }
        array_unshift($args, $propertyName);
        return call_user_func_array(array($this, $methodTarget), $args);
    }
    
    public function getProperty($name)
    {
        return $this->values[$name];
    }
    
    public function setProperty($name, $value)
    {
        $this->values[$name] = $value;
    }
}