<?php

class SResponse implements ArrayAccess
{
    public $values = array();
    
    public function offsetExists($offset)
    {
        if (isset($this->values[$offset])) return true;
        return false;
    }
    
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) return $this->values[$offset];
        return false;
    }
    
    public function offsetSet($offset, $value)
    {
        $this->values[$offset] = $value;
        return;
    }
    
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) unset($this->values[$offset]);
        return;
    }
}

?>
