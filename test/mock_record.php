<?php

class MockRecord
{
    protected $attributes = array();
    protected $values = array();
    
    public function __construct($values = array())
    {
        foreach(func_get_args($values) as $key => $value)
            $this->values[$this->attributes[$key]] = $value;
    }
    
    public function __set($key, $value)
    {
        if (in_array($key, $this->attributes)) $this->values[$key] = $value;
    }
    
    public function __get($key)
    {
        if (isset($this->values[$key])) return $this->values[$key];
        else return null;
    }
}

?>
