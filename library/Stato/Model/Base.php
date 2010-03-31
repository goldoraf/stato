<?php

namespace Stato\Model;

use \Exception;
use Stato\Model\Interfaces\Changeable;

class MethodMissingTargetException extends Exception {}

class Base implements Changeable
{
    protected static $repositories = array();
    
    public static function __callStatic($methodName, $args)
    {
        return call_user_func_array(array(static::getDataset(), $methodName), $args);
    }
    
    public static function create(array $values)
    {
        $class = get_called_class();
        $model = new $class($values);
        $model->save();
        return $model;
    }
    
    protected static function getDataset()
    {
        return new Dataset(static::getMetaclass(), static::getRepository());
    }
    
    public static function getRepository($name = null)
    {
        if (is_null($name)) {
            $name = static::getRepositoryName();
        }
        return Repository::get($name);
    }
    
    public static function getRepositoryName()
    {
        $className = get_called_class();
        if (array_key_exists($className, self::$repositories)) {
            return self::$repositories[$className];
        }
        return static::getDefaultRepositoryName();
    }
    
    public static function getDefaultRepositoryName()
    {
        return Repository::getDefaultName();
    }
    
    public static function setRepositoryName($name)
    {
        self::$repositories[get_called_class()] = $name;
    }
    
    public static function setMetaclass(Metaclass $metaclass)
    {
        $metaclass->defineDynamicMethods('getProperty', 'get');
        $metaclass->defineDynamicMethods('setProperty', 'set');
        $metaclass->setModelClass(get_called_class());
        static::getRepository()->addMetaclass($metaclass);
    }
    
    public static function getMetaclass()
    {
        return static::getRepository()->getMetaclass(get_called_class());
    }
    
    protected $metaclass;
    
    protected $values = array();
    
    protected $changedValues = array();
    
    protected $saved = false;
    
    public function __construct(array $values = null)
    {
        $this->metaclass = static::getMetaclass();
        if (!is_null($values)) {
            $this->metaclass->checkPropertiesExistence(array_keys($values));
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
            = $this->metaclass->getMethodMissingTarget($method, get_called_class());
        if (!method_exists($this, $methodTarget)) {
            throw new MethodMissingTargetException("Call to undefined method target '$methodTarget'");
        }
        array_unshift($args, $propertyName);
        return call_user_func_array(array($this, $methodTarget), $args);
    }
    
    public function getProperty($name)
    {
        if (!$this->metaclass->hasProperty($name)) {
            throw new PropertyMissingException("Missing $name property");
        }
        return array_key_exists($name, $this->values) ? $this->values[$name] : null;
    }
    
    public function setProperty($name, $value)
    {
        if (!$this->metaclass->hasProperty($name)) {
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
            return $this->_create();
        } else {
            return $this->_update();
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
    
    public function setAsSaved()
    {
        $this->saved = true;
    }
    
    protected function _create()
    {
        /* on set les props à leur valeur par défaut sauf pour le serial et les relations */
        static::getRepository()->create($this);
        $this->saved = true;
        $this->changedValues = array();
        return true;
    }
    
    protected function _update()
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