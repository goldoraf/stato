<?php

/**
 * Response
 * 
 * @package 
 * @author goldoraf
 * @copyright Copyright (c) 2004
 * @version 0.1
 * @access public
 **/
class Response implements ArrayAccess
{
    //public $success = True;
    public $values = array();
    //public $errors = array();
    
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
