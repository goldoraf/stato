<?php

class SWebServiceStruct implements ArrayAccess
{
    protected $members = array();
    protected $values  = array();
    
    public function add_member($name, $type)
    {
        $this->members[$name] = $type;
    }
    
    public function member_type($name)
    {
        return $this->members[$name];
    }
    
    public function members_list()
    {
        return $this->members;
    }
    
    public function member_exists($name)
    {
        return in_array($name, array_keys($this->members));
    }
    
    public function __get($name)
    {
        if (!isset($this->values[$name])) return null;
        return $this->values[$name];
    }
    
    public function __set($name, $value)
    {
        if (!$this->member_exists($name)) return false;
        $this->values[$name] = $value;
        return true;
    }
    
    public function to_array()
    {
        return $this->values;
    }
    
    public function offsetExists($offset)
    {
        return $this->member_exists($offset);
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
        if ($this->member_exists($offset)) $this->values[$offset] = null;
    }
}

?>
