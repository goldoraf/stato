<?php

namespace Stato\Model;

use Stato\Model\Query\Condition;
use Stato\Model\Query\Sort;
use Stato\Model\Query\Operators;
use \Exception, \DateTime, \ReflectionObject;

class Property
{
    public $name;
    
    /**
     * PHP type of the property
     */
    public $type;
    
    public $options;
    
    /**
     * String column length (for validation and RDBMS)
     */
    public $length;
    
    public $required;
    
    /**
     * Default value of the property
     */
    public $default;
    
    /**
     * If true, property value is only loaded when on first read
     */
    public $lazy;
    
    /**
     * If true, property may have a null value on save
     */
    public $nullable;
    
    /**
     * If true, property is a key (or a part of a key)
     */
    public $key;
    
    /**
     * If true, column value is auto incrementing
     */
    public $serial;
    
    /**
     * Allows specifying the column in the datastore which the property corresponds to
     * (only applicable for RDBMS or column-oriented stores)
     */
    public $column;
    
    /**
     * If true, an index is created for the property
     */
    public $index;
    
    public $unique;
    
    private $reflector;
    
    public function __construct($name, $type, $options = array())
    {
        $this->validateOptions($options);
        
        $this->name = $name;
        $this->type = $type;
        $this->options = $options;
        
        if (array_key_exists('default', $this->options)) $this->default = $this->options['default'];
        
        $this->key      = $this->popOption('key', $this->type == Metaclass::SERIAL);
        $this->required = $this->popOption('required', $this->key);
        $this->nullable = $this->popOption('nullable', !$this->required);
        $this->length   = $this->popOption('length', null);
        $this->index    = $this->popOption('index', false);
        $this->unique   = $this->popOption('unique', false);
        $this->lazy     = $this->popOption('lazy', false);
        $this->column   = $this->popOption('column', $this->name);
    }
    
    public function get($object)
    {
        if ($object instanceof Base) {
            // TODO : use a getter ?
            return $object->__get($this->name);
        } else {
            $ref = $this->getReflector($object);
            $ref->setAccessible(true);
            return $ref->getValue($object);
        }
    }
    
    public function set($object, $value)
    {
        if ($object instanceof Base) {
            // TODO : use a setter ?
            $object->__set($this->name, $this->typecast($value));
        } else {
            $ref = $this->getReflector($object);
            $ref->setAccessible(true);
            $ref->setValue($object, $this->typecast($value));
        }
    }
    
    public function typecast($value)
    {
        switch ($this->type) {
            case Metaclass::SERIAL:
                return (int) $value;
            case Metaclass::INTEGER:
                return (int) $value;
            case Metaclass::BOOLEAN:
                return ($value == 'true' || $value = 't' || $value == '1') ? true : false;
            case Metaclass::DATE:
                return new DateTime($value);
            case Metaclass::DATETIME:
                return new DateTime($value);
            case Metaclass::TIMESTAMP:
                $date = new DateTime();
                $date->setTimestamp($value);
                return $date;
            case Metaclass::FLOAT:
                return (float) $value;
            default:
                return $value;
        }
    }
    
    public function eq($value)
    {
        return $this->compare(Operators::EQ, $value);
    }
    
    public function ne($value)
    {
        return $this->compare(Operators::NE, $value);
    }
    
    public function lt($value)
    {
        return $this->compare(Operators::LT, $value);
    }
    
    public function le($value)
    {
        return $this->compare(Operators::LE, $value);
    }
    
    public function gt($value)
    {
        return $this->compare(Operators::GT, $value);
    }
    
    public function ge($value)
    {
        return $this->compare(Operators::GE, $value);
    }
    
    public function like($value)
    {
        return $this->compare(Operators::LIKE, $value);
    }
    
    public function in($value)
    {
        return $this->compare(Operators::IN, $value);
    }
    
    public function desc()
    {
        return new Sort($this, false);
    }
    
    public function asc()
    {
        return new Sort($this, true);
    }
    
    private function compare($operator, $value)
    {
        return new Condition($this, $value, $operator);
    }
    
    private function validateOptions(array $options)
    {
        // TODO
        // for example, verify that the type is STRING if there is a length option
    }
    
    private function getReflector($object)
    {
        if (!isset($this->reflector)) {
            $ref = new ReflectionObject($object);
            $this->reflector = $ref->getProperty($this->name);
        }
        return $this->reflector;
    }
    
    private function popOption($optionName, $optionDefault)
    {
        if (!array_key_exists($optionName, $this->options)) return $optionDefault;
        $optionValue = $this->options[$optionName];
        unset($this->options[$optionName]);
        return $optionValue;
    }
}