<?php

abstract class SAssociation
{
    public $assocName       = Null;
    public $assocClass      = Null;
    public $assocTableName  = Null;
    public $assocPrimaryKey = Null;
    public $foreignKey      = Null;
    
    protected $owner = Null;
    protected $target = Null;
    protected $loaded = False;
    protected $options = Null;
    
    public function __construct($owner, $name, $class, $options = array())
    {
        $this->options = $options;
        $this->owner = $owner;
        
        $this->assocName = $name;
        $this->assocClass = $class;
        $this->assocTableName = $options['table_name'];
        $this->assocPrimaryKey = $options['primary_key'];
        $this->foreignKey = $options['foreign_key'];
    }
    
    public function read($reload = false)
    {
        if ($reload) $this->reload();
        $this->loadTarget();
        return $this->target;
    }
    
    public function reload()
    {
        $this->reset();
        return $this->read();
    }
    
    public function isLoaded()
    {
        return $this->loaded;
    }
    
    public function setAsLoaded()
    {
        $this->loaded = True;
    }
    
    abstract public function replace($value);
    
    abstract public function create($attributes=array());
    
    abstract public function build($attributes=array());
    
    // Callbacks
    abstract public function beforeOwnerSave();
    
    abstract public function afterOwnerSave();
    
    abstract public function beforeOwnerDelete();
    
    abstract public function afterOwnerDelete();
    
    protected function loadTarget()
    {
        if (!$this->owner->isNewRecord() || $this->IsFkPresent())
        {
            if (!$this->loaded)
            {
                $this->target = $this->findTarget();
                $this->loaded = true;
            }
        }
    }
    
    // only belongsTo overwrites it
    protected function isFkPresent() 
    {
        return false;
    } 
    
    protected function quotedIds($records)
    {
        $ids = array();
        foreach($records as $record) $ids[] = "'".$record->id."'";
        return $ids;
    }
    
    protected function checkRecordType($record)
    {
        if (!is_object($record) || strtolower(get_class($record)) != $this->assocClass)
        {
            throw new SAssociationTypeMismatch('Bad Record Type');
        }
    }
    
    abstract protected function reset();
    
    abstract protected function findTarget();
}

?>
