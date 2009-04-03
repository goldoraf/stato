<?php

/**
 * ORM manager class
 * 
 * An instance of the <var>SManager</var> class is assigned to the static 
 * <var>$objects</var> property of an autoloaded model class. <var>SManager</var> 
 * instances manage table-level operations and act as "root" querysets that 
 * describes all objects in the model's table.
 * 
 * @package Stato
 * @subpackage mercury
 */
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
    
    /**
     * Returns a new <var>SQuerySet</var> instance describing all objects in the model's table.   
     */
    public function all()
    {
        return $this->get_query_set();
    }
    
    /**
     * Instantiates and populates a new record.
     */
    public function build($attributes = null)
    {
        return $this->get_query_set()->instanciate_record($attributes, null, true);
    }
    
    /**
     * Instantiates, populates and saves a new record.
     */
    public function create($attributes = null)
    {
        $object = $this->build($attributes);
        $object->save();
        return $object;
    }
    
    /**
     * Retrieves and updates attributes of a record.
     */
    public function update($id, $attributes)
    {
        $object = $this->get_query_set()->get($id);
        $object->update_attributes($attributes);
        return $object;
    }
    
    /**
     * Updates all records described by the given SQL <var>$condition</var>.
     */
    public function update_all($set, $condition)
    {
        SActiveRecord::connection()->execute("UPDATE {$this->meta->table_name} SET {$set} WHERE {$condition}");
    }
    
    /**
     * Retrieves a single record or throws a <var>SHttp404</var> exception if it is not found.
     */
    public function get_or_404()
    {
        $args = func_get_args();
        try { return call_user_func_array(array($this, 'get'), $args); }
        catch (Exception $e) { 
            throw new SHttp404();
        }
    }
    
    /**
     * Retrieves a single record or instantiates a new record if it is not found.
     */
    public function get_or_build($attributes)
    {
        try { 
            return $this->get_by_attributes($attributes);
        }
        catch (SRecordNotFound $e) { 
            return $this->build($attributes);
        }
    }
    
    /**
     * Retrieves a single record or instantiates and saves a new record if it is not found.
     */
    public function get_or_create($attributes)
    {
        try { 
            return $this->get_by_attributes($attributes);
        }
        catch (SRecordNotFound $e) { 
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
