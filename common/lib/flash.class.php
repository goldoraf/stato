<?php

class SFlash implements ArrayAccess
{
    private $session = Null;
    
    public function __construct()
    {
        $this->session = SContext::$session;
    }
    
    public function discard()
    {
        unset($this->session['FLASH_MESSAGES']);
    }
    
    public function isEmpty()
    {
        return (count($this->session['FLASH_MESSAGES']) == 0);
    }
    
    public function dump()
    {
        return $this->session['FLASH_MESSAGES'];
    }
    
    public function offsetExists($offset)
    {
        if (isset($this->session['FLASH_MESSAGES'][$offset])) return true;
        return false;
    }
    
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) return $this->session['FLASH_MESSAGES'][$offset];
        return null;
    }
    
    public function offsetSet($offset, $value)
    {
        // we can't simply use $this->session['FLASH_MESSAGES'][$offset] = $value
        // because it throws a fatal error : : Objects used as arrays in post/pre 
        // increment/decrement must return values by reference
        // cf http://bugs.php.net/bug.php?id=34816 & http://bugs.php.net/bug.php?id=30616
        $messages = $this->session['FLASH_MESSAGES'];
        $messages[$offset] = $value;
        $this->session['FLASH_MESSAGES'] = $messages;
        return;
    }
    
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) unset($this->session['FLASH_MESSAGES'][$offset]);
        return;
    }
}

?>
