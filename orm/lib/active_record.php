<?php

class Stato_ActiveRecord
{
    protected $values;
    
    public function __construct(array $values = array())
    {
        /*$this->meta = SMapper::retrieve(get_class($this));
        $this->ensure_proper_type();*/
        $this->populate($values);
    }
    
    public function __get($name)
    {
        $accMethod = 'get'.$name;
        if (method_exists($this, $accMethod)) return $this->$accMethod();
        return $this->getProperty($name);
    }
    
    public function __set($name, $value)
    {
        $accMethod = 'set'.$name;
        if (method_exists($this, $accMethod)) return $this->$accMethod();
        return $this->setProperty($name, $value);
    }
    
    protected function getProperty($name)
    {
        
    }
    
    protected function setProperty($name, $value)
    {
        
    }
    
    protected function populate(array $values)
    {
        
    }
}