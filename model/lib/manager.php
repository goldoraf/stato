<?php

class SManager
{
    protected $meta = null;
    
    public function __construct($class)
    {
        $this->meta = SActiveRecordMeta::retrieve($class);
    }
    
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->getQuerySet(), $method), $args);
    }
    
    public function all()
    {
        return $this->getQuerySet();
    }
    
    public function create($attributes = null)
    {
        $class = $this->meta->class;
        $object = new $class($attributes);
        $object->save();
        return $object;
    }
    
    /*public function getOrCreate()
    {
        try { return call_user_func_array(array($this, 'get'), func_get_args()); }
        catch (SActiveRecordDoesNotExist $e) { 
            return call_user_func_array(array($this, 'create'), func_get_args());
        }
    }*/
    
    protected function getQuerySet()
    {
        return new SQuerySet($this->meta);
    }
}

?>
