<?php

class SManager
{
    protected $meta = null;
    
    public function __construct($class)
    {
        $this->meta = SMapper::retrieve($class);
    }
    
    public function __call($method, $args)
    {
        if (preg_match('/^(get|all)_by_([_a-zA-Z]\w*)$/', $method, $matches))
        {
            $method = ($matches[1] == 'get') ? 'get' : 'filter';
            $attributes = explode('_and_', $matches[2]);
            $values = $args; $args = array();
            foreach ($attributes as $attr) $args[] = "$attr = ?";
            $args[] = $values;
        }
        return call_user_func_array(array($this->get_query_set(), $method), $args);
    }
    
    public function all()
    {
        return $this->get_query_set();
    }
    
    public function build($attributes = null)
    {
        return $this->get_query_set()->instanciate_record($attributes, null, true);
    }
    
    public function create($attributes = null)
    {
        $object = $this->build($attributes);
        $object->save();
        return $object;
    }
    
    public function update($id, $attributes)
    {
        $object = $this->get_query_set()->get($id);
        $object->update_attributes($attributes);
        return $object;
    }
    
    public function update_all($set, $condition)
    {
        SActiveRecord::connection()->execute("UPDATE {$this->meta->table_name} SET {$set} WHERE {$condition}");
    }
    
    public function get_or_404()
    {
        $args = func_get_args();
        try { return call_user_func_array(array($this, 'get'), $args); }
        catch (Exception $e) { 
            throw new SHttp404();
        }
    }
    
    public function get_or_build($attributes)
    {
        try { 
            return $this->get_by_attributes($attributes);
        }
        catch (SActiveRecordDoesNotExist $e) { 
            return $this->build($attributes);
        }
    }
    
    public function get_or_create($attributes)
    {
        try { 
            return $this->get_by_attributes($attributes);
        }
        catch (SActiveRecordDoesNotExist $e) { 
            return $this->create($attributes);
        }
    }
    
    protected function get_by_attributes($attributes)
    {
        $args = array();
        foreach ($attributes as $k => $v) $args[] = "$k = :$k";
        $args[] = $attributes;
        return call_user_func_array(array($this, 'get'), $args);
    }
    
    protected function get_query_set()
    {
        return new SQuerySet($this->meta);
    }
}

?>
