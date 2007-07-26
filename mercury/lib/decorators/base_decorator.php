<?php

class SActiveRecordDecorator
{
    protected $record = null;
    
    public function __construct($record, $config = array())
    {
        $this->record = $record;
    }
    
    public function __get($name)
    {
        return $this->record->$name;
    }
    
    public function __set($name, $value)
    {
        $this->record->$name = $value;
    }
    
    public function __call($method_missing, $args)
    {
        return call_user_func_array(array($this->record, $method_missing), $args);
    }
}

?>
