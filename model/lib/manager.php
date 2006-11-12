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
        return call_user_func_array(array($this->get_query_set(), $method), $args);
    }
    
    public function all()
    {
        return $this->get_query_set();
    }
    
    public function create($attributes = null)
    {
        $class = $this->meta->class;
        $object = new $class($attributes);
        $object->save();
        return $object;
    }
    
    public function update($id, $attributes)
    {
        $object = $this->get_query_set()->get($id);
        $object->update_attributes($attributes);
        return $object;
    }
    
    /*public function get_or_create()
    {
        try { return call_user_func_array(array($this, 'get'), func_get_args()); }
        catch (SActiveRecordDoesNotExist $e) { 
            return call_user_func_array(array($this, 'create'), func_get_args());
        }
    }*/
    
    protected function get_query_set()
    {
        return new SQuerySet($this->meta);
    }
}

?>
