<?php

class SSession implements ArrayAccess
{
    public function __construct()
    {
        session_start();
    }
    
    public function destroy()
    {
        $_session = array();
        session_destroy();
    }
    
    public function session_id()
    {
        return session_id();
    }
    
    public function offsetExists($offset)
    {
        if (isset($_session[$offset])) return true;
        return false;
    }
    
    public function offsetGet($offset)
    {
        if ($this->offset_exists($offset)) return $_session[$offset];
        return null;
    }
    
    public function offsetSet($offset, $value)
    {
        $_session[$offset] = $value;
        return;
    }
    
    public function offsetUnset($offset)
    {
        if ($this->offset_exists($offset)) unset($_session[$offset]);
        return;
    }
}

?>
