<?php

class Entity extends Observable implements ArrayAccess
{
    public $attributes    = array();
    public $relationships = array();
    public $attrRequired  = array();
    
    /**
     * Attributes protected from mass-assignment (populate() et constructor)
     */
    public $attrProtected = array();
    public $attrUnique    = array();
    public $validations   = array();
    public $errors        = array();
    
    protected $values     = array();
    protected $assocs     = array();
    
    public function __construct($values = Null)
    {
        $this->initAttributes();
        $this->initValues();
        if ($values != Null && is_array($values)) $this->populate($values); 
    }
    
    public function __get($name)
    {
        $accMethod = 'read'.ucfirst($name);
        if (method_exists($this, $accMethod)) return $this->$accMethod();
        elseif ($this->assocExists($name)) return $this->readAssociation($name);
        else return $this->readAttribute($name);
    }
    
    public function __set($name, $value)
    {
        $accMethod = 'write'.ucfirst($name);
        if (method_exists($this, $accMethod)) return $this->$accMethod($value);
        elseif ($this->assocExists($name)) return $this->writeAssociation($name, $value);
        else return $this->writeAttribute($name, $value);
    }
    
    public function offsetExists($offset)
    {
        if ($this->attrExists($offset) || $this->assocExists($offset)) return true;
        return false;
    }
    
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }
    
    public function offsetSet($offset, $value)
    {
        return $this->__set($offset, $value);
    }
    
    public function offsetUnset($offset)
    {
        return $this->__set($offset, Null);
    }
    
    public function __repr()
    {
        
    }
    
    public function __toString()
    {
        $str = '';
        foreach($this->values as $key => $value)
        {
            if ($value === True) $value = 'True';
            if ($value === False) $value = 'False';
            if ($value === Null) $value = 'Null';
            $str.= "$key = $value\n";
        }
        return '['.get_class($this)."]\n".$str;
    }
    
    public function isValid()
    {
        $this->errors = array();
        $this->runValidations();
        $this->validate();
        return empty($this->errors);
    }
    
    /**
     * Overwrite this method for check validations on all saves
     */
    public function validate()
    {
    
    }
    
    public function contentAttributes()
    {
        return array_keys($this->attributes);
    }
    
    protected function attrExists($name)
    {
        return array_key_exists($name, $this->attributes);
    }
    
    protected function assocExists($name)
    {
        return array_key_exists($name, $this->relationships);
    }
    
    protected function readAttribute($name)
    {
        if ($this->attrExists($name))
            return $this->attributes[$name]->typecast($this->values[$name]);
        else
            return $this->values[$name];
    }
    
    protected function readAttributeBeforeTypecast($name)
    {
        return $this->values[$name];
    }
    
    protected function writeAttribute($name, $value)
    {
        if ($this->attrExists($name) 
            && in_array($this->attributes[$name]->type, array('boolean', 'integer', 'float')))
            $this->values[$name] = $this->convertNumberValue($value);
        else
            $this->values[$name] = $value;
        return true;
    }
    
    protected function readAssociation($name)
    {
        if (isset($this->assocs[$name])) return $this->assocs[$name];
        else return Null;
    }
    
    protected function writeAssociation($name, $value)
    {
        // on check le nom de la classe de $value (== $this->relationships[$name]['dest'])
        // ou bien $value doit être un array si la relation est de type 'to_many'
        $this->assocs[$name] = $value;
        return true;
    }
    
    /**
     * Sets all attributes at once by passing in an array with keys matching the
     * attribute names.
     */
    protected function populate($values=array())
    {
        $multiParamsAttributes = array();
        
        foreach($values as $key => $value)
        {
            if (is_array($value)) $multiParamsAttributes[$key] = $value;
            elseif (!in_array($key, $this->attrProtected)) $this->$key = stripslashes($value);
        }
        
        if (!empty($multiParamsAttributes)) $this->assignMultiparamsAttributes($multiParamsAttributes);
    }
    
    protected function convertNumberValue($value)
    {
        if ($value === True)  return 1;
        if ($value === False) return 0;
        if ($value == '')     return Null;
        
        return $value;
    }
    
    /**
     * Creates string values for all attributes that needs more than one single parameter
     * (such as Dates).
     */
    protected function assignMultiparamsAttributes($params)
    {
        $errors = array();
        foreach($params as $key => $value)
        {
            $type = $this->attributes[$key]->type;
            switch ($type)
            {
                case 'date':
                    $this->$key = $value['year'].'-'.$value['month'].'-'.$value['day'];
                    break;
                case 'datetime':
                    $this->$key = $value['year'].'-'.$value['month'].'-'.$value['day']
                                  .' '.$value['hour'].':'.$value['min'].':'.$value['sec'];
                    break;
                case 'list':
                    $this->$key = implode('|', $value);
                    break;
            }
        }
    }
    
    protected function runValidations($method)
    {
        foreach(array_keys($this->values) as $key)
        {
            Validation::validateAttribute($this, $key, $method);
        }
    }
    
    /**
     * Replaces $attributes metadata array by an array of instantiated objects.
     * This method is called in case of user-defined attributes.
     */
    protected function initAttributes()
    {
        $attributes = $this->attributes;
        $this->attributes = array();
        
        foreach($attributes as $name => $options)
        {
            if (!is_array($options))
            {
                $type = $options;
                $options = array();
            }
            else
            {
                $type = $options['type'];
                unset($options['type']);
            } 
            
            $default = Null;
            if (isset($options['default']))
            {
                $default = $options['default'];
                unset($options['default']);
            }
                
            $this->values[$name] = $default;
            $this->attributes[$name] = new Attribute($name, $type, $default, $options, True);
            
            if ($this->attributes[$name]->options['required'] === True) $this->attrRequired[] = $name;
            if ($this->attributes[$name]->options['unique'] === True) $this->attrUnique[] = $name;
            if ($this->attributes[$name]->options['protected'] === True) $this->attrProtected[] = $name;
            
            if (!empty($this->attributes[$name]->options['validations']))
                $this->validations[$name] = $this->attributes[$name]->options['validations'];
        }
    }
    
    /**
     * Initializes $values array with default values for all attributes.
     *
     * NB : this method is not really necessary : we could return directly default attribute value
     * from readAttribute($name) if $this->values[$name] is not set. But for unit testing, we need
     * to compare 2 entities with assertEqual(), and it doesn't work if the $values array is not initialized
     */
    protected function initValues()
    {
        foreach($this->attributes as $key => $attr) $this->$key = $attr->default;
    }
}

?>
