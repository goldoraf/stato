<?php

namespace Stato\Model;

use Stato\Model\Query\Condition;
use Stato\Model\Query\Operators;
use \Exception;

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
    
    public function __construct($name, $type, $options = array())
    {
        $this->name = $name;
        $this->type = $type;
        $this->options = $options;
        
        if (array_key_exists('column',  $this->options)) $this->column  = $this->options['column'];
        if (array_key_exists('default', $this->options)) $this->default = $this->options['default'];
        
        $this->key      = $this->popOption('key', $this->type == Metaclass::SERIAL);
        $this->required = $this->popOption('required', $this->key);
        $this->nullable = $this->popOption('nullable', !$this->required);
        $this->index    = $this->popOption('nullable', false);
        $this->lazy     = $this->popOption('nullable', false);
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
    
    private function compare($operator, $value)
    {
        return new Condition($this, $value, $operator);
    }
    
    private function popOption($optionName, $optionDefault)
    {
        if (!array_key_exists($optionName, $this->options)) return $optionDefault;
        $optionValue = $this->options[$optionName];
        unset($this->options[$optionName]);
        return $optionValue;
    }
}