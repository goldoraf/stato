<?php

abstract class SAbstractSession implements ArrayAccess
{
    protected $data = array();
    
    abstract public function destroy();
    
    abstract public function store();
    
    abstract public function session_id();
    
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }
    
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) return $this->data[$offset];
        return null;
    }
    
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
        return;
    }
    
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) unset($this->data[$offset]);
        return;
    }
}

?>
