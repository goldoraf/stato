<?php

namespace Stato\Model;

class MethodMissingException extends Exception {}
class PropertyMissingException extends Exception {}

class Metadata
{
    const STRING = 'string';
    
    const DATETIME = 'datetime';
    
    const INTEGER = 'integer';
    
    const FLOAT = 'float';
    
    private $properties = array();
    
    private $methods = array();
    
    public function addProperty($name)
    {
        $this->properties[$name] = array('type' => self::STRING);
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
}