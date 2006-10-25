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
        $manager = $this->meta->getManager($owner);
        $manager->replace($value);
        return $manager;
    }
    
    public function defaultValue($owner)
    {
        return $this->meta->getManager($owner);
    }
}

class SAssociationMeta
{
    public $type  = null;
    public $class = null;
    public $foreignKey = null;
    
    protected $validOptions = array('assoc_type', 'class_name', 'foreign_key');
    protected $baseMeta = null;
    
    public static function getInstance($ownerMeta, $assocName, $options)
    {
        if (!is_array($options))
        {
            $type = $options;
            $options = array();
            $options['assoc_type'] = $type;
        }
        if (!isset($options['assoc_type'])) throw new SException('Type of relationship is required.');
        $options['assoc_type'] = 'S'.SInflection::camelize($options['assoc_type']);
        $instanceClass = $options['assoc_type'].'Meta';
        return new $instanceClass($ownerMeta, $assocName, $options);
    }
    
    public function __construct($ownerMeta, $assocName, $options)
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
                    SDependencies::requireDependency('models', $options['class_name'], $ownerMeta->class);
            }
        }
        else
        {
            if ($this->type == 'SHasMany' || $this->type == 'SManyToMany') 
                $this->class = SInflection::camelize(SInflection::singularize($assocName));
            else 
                $this->class = SInflection::camelize($assocName);
        }
        
        if (!class_exists($this->class))
            SDependencies::requireDependency('models', $this->class, $ownerMeta->class);
    }
    
    public function __get($key)
    {
        return $this->baseMeta()->$key;
    }
    
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->baseMeta(), $method), $args);
    }
    
    public function getManager($owner)
    {
        $class = $this->type.'Manager';
        return new $class($owner, $this);
    }
    
    protected function assertValidOptions($options, $additionalOptions = array())
    {
        $validOptions = array_merge($this->validOptions, $additionalOptions);
        foreach(array_keys($options) as $key)
        {
            if (!in_array($key, $validOptions))
                throw new SException($key.' is not a valid mapping option.');
        }
    }
    
    protected function baseMeta()
    {
        if ($this->baseMeta === null) $this->baseMeta = SActiveRecordMeta::retrieve($this->class);
        return $this->baseMeta;
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
        if (!$this->owner->isNewRecord() || $this->isFkPresent())
        {
            if (!$this->loaded)
            {
                $this->target = $this->findTarget();
                $this->loaded = true;
            }
        }
        return $this->target;
    }
    
    public function setTarget($record)
    {
        $this->target = $record;
        $this->loaded = true;
    }
    
    public function isLoaded()
    {
        return $this->loaded;
    }
    
    public function isNull()
    {
        return $this->target() === null;
    }
    
    // only belongsTo overwrites it
    protected function isFkPresent() 
    {
        return false;
    }
    
    protected function checkRecordType($record)
    {
        if (!is_object($record) || get_class($record) != $this->meta->class)
            throw new SAssociationTypeMismatch('Bad Record Type');
    }
    
    abstract public function replace($value);
    
    // Callbacks
    public function beforeOwnerSave() {}
    
    public function afterOwnerSave() {}
    
    public function beforeOwnerDelete() {}
    
    public function afterOwnerDelete() {}
    
    abstract protected function findTarget();
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
            return call_user_func_array(array($this->getQuerySet(), $method), $args);
    }
    
    public function all()
    {
        if ($this->owner->isNewRecord()) return $this->unsaved;
        if ($this->qs !== null) return $this->qs;
        return $this->getQuerySet();
    }
    
    public function count()
    {
        if ($this->owner->isNewRecord()) return count($this->unsaved);
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
            $this->checkRecordType($record);
            if ($this->owner->isNewRecord()) $this->unsaved[] = $record;
            else $this->insertRecord($record);
        }
    }
    
    public function delete($records)
    {
        if (!is_array($records)) $records = array($records);
        foreach($records as $record)
        {
            $this->checkRecordType($record);
            $this->deleteRecord($record);
        }
    }
    
    public function replace($records)
    {
        $this->clear();
        $this->add($records);
    }
    
    public function setQuerySet($qs)
    {
        $this->qs = $qs;
    }
    
    public function isLoaded()
    {
        return ($this->qs !== null);
    }
    
    abstract public function clear();
    
    public function beforeOwnerSave() {}
    
    public function afterOwnerSave()
    {
        foreach ($this->unsaved as $record) $this->insertRecord($record);
    }
    
    public function beforeOwnerDelete() {}
    
    public function afterOwnerDelete() {}
    
    protected function checkRecordType($record)
    {
        if (!is_object($record) || get_class($record) != $this->meta->class)
            throw new SAssociationTypeMismatch('Bad Record Type');
    }
    
    protected function connection()
    {
        return SActiveRecord::connection();
    }
    
    abstract protected function insertRecord($record);
    
    abstract protected function deleteRecord($record);
    
    abstract protected function getQuerySet();
}

?>
