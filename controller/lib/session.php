<?php

class SSession implements ArrayAccess
{
    public function __construct()
    {
        session_start();
    }
    
    public function destroy()
    {
        $_SESSION = array();
        session_destroy();
    }
    
    public function sessionId()
    {
        return session_id();
    }
    
    public function offsetExists($offset)
    {
        if (isset($_SESSION[$offset])) return true;
        return false;
    }
    
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) return $_SESSION[$offset];
        return null;
    }
    
    public function offsetSet($offset, $value)
    {
        $_SESSION[$offset] = $value;
        return;
    }
    
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) unset($_SESSION[$offset]);
        return;
    }
}

?>
