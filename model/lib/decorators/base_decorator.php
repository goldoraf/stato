<?php

class SActiveRecordDecorator
{
    protected $record = null;
    
    public function __construct($record)
    {
        $this->record = $record;
    }
    
    public function __get($name)
    {
        return $this->record->__get($name);
    }
    
    public function __set($name, $value)
    {
        return $this->record->__set($name, $value);
    }
    
    public function __call($methodMissing, $args)
    {
        return call_user_func_array(array($this->record, $methodMissing), $args);
    }
}

?>
