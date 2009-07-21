<?php

class STableMap
{
    public $class              = null;
    public $underscored        = null;
    public $table_name         = null;
    public $identity_field     = 'id';
    public $inheritance_field  = 'type';
    public $attributes         = array();
    public $relationships      = array();
    public $columns            = array();
    public $associations       = array();
    public $decorators         = array();
    
    private $content_attributes       = null;
    private $content_attributes_names = null;
    private $possible_fk              = null;
    private $associations_loaded      = false;
    
    public function __construct($class)
    {
        $this->class = $class;
        $this->underscored = SInflection::underscore($class);
        $this->get_meta_from_class();
        if ($this->table_name === null) $this->reset_table_name();
        $this->attributes = $this->columns = SActiveRecord::connection()->columns($this->table_name);
    }
    
    public function load_associations()
    {
        if ($this->associations_loaded) return;
        $this->associations = $this->instantiate_associations();
        $this->attributes = array_merge($this->attributes, $this->associations);
        $this->associations_loaded = true;
    }
    
    public function reset_table_name()
    {
        if (($parent = $this->descends_from()) == 'SActiveRecord')
            $this->table_name = SInflection::pluralize(SInflection::underscore($this->class));
        else
            $this->table_name = SInflection::pluralize(SInflection::underscore($this->get_real_parent_class()));
            
        if (SActiveRecord::$table_name_prefix !== null)
            $this->table_name = SActiveRecord::$table_name_prefix.'_'.$this->table_name;
        if (SActiveRecord::$table_name_suffix !== null)
            $this->table_name.= '_'.SActiveRecord::$table_name_suffix;
    }
    
    public function descends_from()
    {
        $ref = new ReflectionClass($this->class);
        return $ref->getParentClass()->getName();
    }
    
    public function content_attributes($filter = array())
    {
        if ($this->content_attributes === null)
        {
            $this->content_attributes = array();
            foreach ($this->columns as $name => $attr)
                if ($name != $this->identity_field && !preg_match('/(_id|_count)$/', $name) 
                    && $name != $this->inheritance_field && !in_array($name, $filter))
                    if ($this->content_attributes_names === null || in_array($name, $this->content_attributes_names))
                        $this->content_attributes[$name] = $attr;
        }
               
        return $this->content_attributes;
    }
    
    public function get_possible_fk()
    {
        if ($this->possible_fk === null)
        {
            if (($parent = $this->descends_from()) == 'SActiveRecord')
                $this->possible_fk = $this->underscored.'_id';
            else
                $this->possible_fk = SInflection::underscore($this->get_real_parent_class()).'_id';
        }
        return $this->possible_fk;
    }
    
    protected function get_real_parent_class()
    {
        $parent = $this->descends_from();
        do {
            $ref = new ReflectionClass($parent);
            $parent = $ref->getParentClass()->getName();
        } while ($parent != 'SActiveRecord');
        return $ref->getName();
    }
    
    protected function instantiate_associations()
    {
        $assocs = array();
        foreach ($this->relationships as $name => $options) 
            $assocs[$name] = new SAssociation(SAssociationMeta::get_instance($this, $name, $options));
        return $assocs;
    }
    
    protected function get_meta_from_class()
    {
        $ref = new ReflectionClass($this->class);
        $props = array('table_name', 'identity_field', 'inheritance_field', 'relationships', 'decorators', 'content_attributes_names');
        foreach ($props as $p) 
            if ($ref->hasProperty($p)) $this->$p = $ref->getStaticPropertyValue($p);
        
        foreach ($this->decorators as $decorator => $config)
        {
            $decorator_class = 'S'.SInflection::camelize($decorator).'Decorator';
            if (!class_exists($decorator_class, false))
                throw new Exception("Unknown decorator $decorator");
            if (method_exists($decorator_class, 'alter_table_map'))
                call_user_func(array($decorator_class, 'alter_table_map'), $this, $config);
        }
    }
}

?>
