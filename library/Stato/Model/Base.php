<?php

namespace Stato\Model;

use \Exception;
use Stato\Model\Interfaces\Changeable;

class MethodMissingTargetException extends Exception {}

class Base implements Changeable
{
    protected static $metadata;
    
    protected static $repositories = array();
    
    public static function setMetadata(Metadata $meta)
    {
        $meta->defineDynamicMethods('getProperty', 'get');
        $meta->defineDynamicMethods('setProperty', 'set');
        self::$metadata[get_called_class()] = $meta;
    }
    
    public static function getRepository()
    {
        return Repository::get(self::getRepositoryName());
    }
    
    public static function getRepositoryName()
    {
        if (array_key_exists(get_called_class(), self::$repositories)) {
            return self::$repositories[get_called_class()];
        }
        return self::getDefaultRepositoryName();
    }
    
    public static function getDefaultRepositoryName()
    {
        return Repository::getDefaultName();
    }
    
    public static function setRepositoryName($name)
    {
        self::$repositories[get_called_class()] = $name;
    }
    
    protected $values = array();
    
    protected $changedValues = array();
    
    protected $saved = false;
    
    public function __construct(array $values = null)
    {
        if (!is_null($values)) {
            $this->getMetadata()->checkPropertiesExistence(array_keys($values));
            foreach ($values as $k => $v) $this->__set($k, $v);
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
            = $this->getMetadata()->getMethodMissingTarget($method, get_called_class());
        if (!method_exists($this, $methodTarget)) {
            throw new MethodMissingTargetException("Call to undefined method target '$methodTarget'");
        }
        array_unshift($args, $propertyName);
        return call_user_func_array(array($this, $methodTarget), $args);
    }
    
    public function getMetadata()
    {
        $class = get_class($this);
        if (!isset(self::$metadata[$class])) {
            throw new Exception("No metadata class assigned to class $class");
        }
        return self::$metadata[$class];
    }
    
    public function getProperty($name)
    {
        if (!$this->getMetadata()->hasProperty($name)) {
            throw new PropertyMissingException("Missing $name property");
        }
        return array_key_exists($name, $this->values) ? $this->values[$name] : null;
    }
    
    public function setProperty($name, $value)
    {
        if (!$this->getMetadata()->hasProperty($name)) {
            throw new PropertyMissingException("Missing $name property");
        }
        $this->trackChange($name, $value);
        $this->values[$name] = $value;
    }
    
    public function save()
    {
        if (!$this->hasChanged()) {
            return $this->isSaved();
        }
        if ($this->isNew()) {
            return $this->create();
        } else {
            return $this->update();
        }
    }
    
    public function isNew()
    {
        return !$this->isSaved();
    }
    
    public function isSaved()
    {
        return $this->saved == true;
    }
    
    protected function create()
    {
        /*properties.each do |property|
        unless property.serial? || property.loaded?(self)
          property.set(self, property.default_for(self))
        end
      end*/
      static::getRepository()->create($this);
      $this->saved = true;
      $this->changedValues = array();
      return true;
    }
    
    protected function update()
    {
        
    }
    
    /**
     * Do any properties have unsaved changes?
     */
    public function hasChanged()
    {
        return count($this->changedValues) != 0;
    }
    
    /**
     * Returns an array of properties with unsaved changes.
     * <code>$person->getChangedProperties();   // -> false
     * $person->setName('john');
     * $person->getChangedProperties();   // -> array('name')</code>             
     */
    public function getChangedProperties()
    {
        return array_keys($this->changedValues);
    }
    
    public function getChangedValues()
    {
        return $this->changedValues;
    }
    
    /**
     * Does <var>$name</var> property have unsaved change?
     */
    public function hasPropertyChanged($name)
    {
        return in_array($name, $this->getChangedProperties());
    }
    
    public function getChanges()
    {
        $changes = array();
        foreach ($this->changedValues as $k => $v) {
            $changes[$k] = array($v, $this->getProperty($k));
        }
        return $changes;
    }
    
    protected function trackChange($name, $value)
    {
        $old = $this->getProperty($name);
        if ($this->hasValueChanged($name, $old, $value)) {
            $this->changedValues[$name] = $old;
        }
    }
    
    protected function hasValueChanged($name, $old, $value)
    {
        return (empty($old) && !empty($value)) || $old != $value;
    }
}