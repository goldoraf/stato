<?php

class SAssociation
{
    public $meta = null;
    
    public function __construct($meta)
    {
        $this->meta = $meta;
    }
    
    public function typecast($owner, $value)
    {
        $manager = $this->meta->get_manager($owner);
        $manager->replace($value);
        return $manager;
    }
    
    public function default_value($owner)
    {
        return $this->meta->get_manager($owner);
    }
}

class SAssociationMeta
{
    public $type  = null;
    public $class = null;
    public $foreign_key = null;
    
    protected $valid_options = array('assoc_type', 'class_name', 'foreign_key');
    protected $base_meta = null;
    
    public static function get_instance($owner_meta, $assoc_name, $options)
    {
        if (!is_array($options))
        {
            $type = $options;
            $options = array();
            $options['assoc_type'] = $type;
        }
        if (!isset($options['assoc_type'])) throw new SException('Type of relationship is required.');
        $options['assoc_type'] = 'S'.SInflection::camelize($options['assoc_type']);
        $instance_class = $options['assoc_type'].'Meta';
        return new $instance_class($owner_meta, $assoc_name, $options);
    }
    
    public function __construct($owner_meta, $assoc_name, $options)
    {
        $this->type = $options['assoc_type'];
        
        if (isset($options['class_name']))
        {
            if (strpos($options['class_name'], '/') === false)
                $this->class = SInflection::camelize($options['class_name']);
            else
            {
                list($subdir, $class) = explode('/', $options['class_name']);
                $this->class = SInflection::camelize($class);
                
                if (!class_exists($this->class))
                    SDependencies::require_dependency('models', $options['class_name'], $owner_meta->class);
            }
        }
        else
        {
            if ($this->type == 'SHasMany' || $this->type == 'SManyToMany') 
                $this->class = SInflection::camelize(SInflection::singularize($assoc_name));
            else 
                $this->class = SInflection::camelize($assoc_name);
        }
        
        if (!class_exists($this->class))
            SDependencies::require_dependency('models', $this->class, $owner_meta->class);
    }
    
    public function __get($key)
    {
        return $this->base_meta()->$key;
    }
    
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->base_meta(), $method), $args);
    }
    
    public function get_manager($owner)
    {
        $class = $this->type.'Manager';
        return new $class($owner, $this);
    }
    
    protected function assert_valid_options($options, $additional_options = array())
    {
        $valid_options = array_merge($this->valid_options, $additional_options);
        foreach(array_keys($options) as $key)
        {
            if (!in_array($key, $valid_options))
                throw new SException($key.' is not a valid mapping option.');
        }
    }
    
    protected function base_meta()
    {
        if ($this->base_meta === null) $this->base_meta = SMapper::retrieve($this->class);
        return $this->base_meta;
    }
}

abstract class SAssociationManager
{
    protected $owner  = null;
    protected $meta   = null;
    protected $target = null;
    protected $loaded = false;
    
    public function __construct($owner, $meta)
    {
        $this->owner = $owner;
        $this->meta  = $meta;
    }
    
    public function __get($key)
    {
        return $this->target()->$key;
    }
    
    public function __set($key, $value)
    {
        $this->target()->$key = $value;
    }
    
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->target(), $method), $args);
    }
    
    public function create($attributes=array())
    {
        $class = $this->meta->class;
        $record = new $class($attributes);
        $record->save();
        $this->replace($record);
        return $record;
    }
    
    public function target()
    {
        if (!$this->owner->is_new_record() || $this->is_fk_present())
        {
            if (!$this->loaded)
            {
                $this->target = $this->find_target();
                $this->loaded = true;
            }
        }
        return $this->target;
    }
    
    public function set_target($record)
    {
        $this->target = $record;
        $this->loaded = true;
    }
    
    public function is_loaded()
    {
        return $this->loaded;
    }
    
    public function is_null()
    {
        return $this->target() === null;
    }
    
    // only belongsTo overwrites it
    protected function is_fk_present() 
    {
        return false;
    }
    
    protected function check_record_type($record)
    {
        $ref = new ReflectionObject($record);
        
        if (!is_object($record) && get_class($record) != $this->meta->class 
            && $ref->getParentClass()->getName() != $this->meta->class)
            throw new SAssociationTypeMismatch('Bad Record Type');
    }
    
    abstract public function replace($value);
    
    // Callbacks
    public function before_owner_save() {}
    
    public function after_owner_save() {}
    
    public function before_owner_delete() {}
    
    public function after_owner_delete() {}
    
    abstract protected function find_target();
}

abstract class SManyAssociationManager
{
    protected $owner   = null;
    protected $meta    = null;
    protected $qs      = null;
    protected $unsaved = array();
    
    public function __construct($owner, $meta)
    {
        $this->owner = $owner;
        $this->meta  = $meta;
    }
    
    public function __call($method, $args)
    {
        if ($this->qs !== null) 
            return call_user_func_array(array($this->qs, $method), $args);
        else
            return call_user_func_array(array($this->get_query_set(), $method), $args);
    }
    
    public function all()
    {
        if ($this->owner->is_new_record()) return $this->unsaved;
        if ($this->qs !== null) return $this->qs;
        return $this->get_query_set();
    }
    
    public function count()
    {
        if ($this->owner->is_new_record()) return count($this->unsaved);
        return $this->__call('count', array());
    }
    
    public function create($attributes=array())
    {
        $class = $this->meta->class;
        $record = new $class($attributes);
        $record->save();
        $this->add($record);
        return $record;
    }
    
    public function add($records)
    {
        if (!is_array($records)) $records = array($records);
        foreach($records as $record)
        {
            $this->check_record_type($record);
            if ($this->owner->is_new_record()) $this->unsaved[] = $record;
            else $this->insert_record($record);
        }
    }
    
    public function delete($records)
    {
        if (!is_array($records)) $records = array($records);
        foreach($records as $record)
        {
            $this->check_record_type($record);
            $this->delete_record($record);
        }
    }
    
    public function replace($records)
    {
        $this->clear();
        $this->add($records);
    }
    
    public function singular_ids($ids)
    {
        $qs = new SQuerySet($this->meta);
        $this->replace($qs->get($ids));
    }
    
    public function set_query_set($qs)
    {
        $this->qs = $qs;
    }
    
    public function is_loaded()
    {
        return ($this->qs !== null);
    }
    
    abstract public function clear();
    
    public function before_owner_save() {}
    
    public function after_owner_save()
    {
        foreach ($this->unsaved as $record) $this->insert_record($record);
    }
    
    public function before_owner_delete() {}
    
    public function after_owner_delete() {}
    
    protected function check_record_type($record)
    {
        $ref = new ReflectionObject($record);
        
        if (!is_object($record) && get_class($record) != $this->meta->class 
            && $ref->getParentClass()->getName() != $this->meta->class)
            throw new SAssociationTypeMismatch('Bad Record Type');
    }
    
    protected function connection()
    {
        return SActiveRecord::connection();
    }
    
    abstract protected function insert_record($record);
    
    abstract protected function delete_record($record);
    
    abstract protected function get_query_set();
}

?>
