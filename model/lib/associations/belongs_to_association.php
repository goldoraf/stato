<?php

class SBelongsToMeta extends SAssociationMeta
{
    public function __construct($ownerMeta, $assocName, $options)
    {
        parent::__construct($ownerMeta, $assocName, $options);
        $this->assertValidOptions($options);
        if (isset($options['foreign_key'])) $this->foreignKey = $options['foreign_key'];
        else $this->foreignKey = SInflection::underscore($this->class).'_id';
    }
}

class SBelongsToManager extends SAssociationManager
{
    public function replace($record)
    {
        if ($record === null)
        {
            $this->target = null;
            $this->owner[$this->meta->foreignKey] = null;
        }
        else
        {
            $this->checkRecordType($record);
            $this->target = $record;
            if (!$record->isNewRecord()) $this->owner[$this->meta->foreignKey] = $record->id;
        }
        $this->loaded = true;
    }
    
    public function create($attributes=array())
    {
        $class = $this->meta->class;
        $record = new $class($attributes);
        $record->save();
        $this->replace($record);
        return $record;
    }
    
    public function build($attributes=array())
    {
        $class = $this->meta->class;
        $record = new $class($attributes);
        $this->replace($record);
        return $record;
    }
    
    public function beforeOwnerSave()
    {
        if ($this->target !== null)
        {
            if ($this->target->isNewRecord()) $this->target->save();
            $this->owner[$this->meta->foreignKey] = $this->target->id;
        }
    }
    
    protected function findTarget()
    {
        //if ($this->owner->isNewRecord() || $this->owner[$this->meta->foreignKey] === null) return null;
        $qs = new SQuerySet($this->meta);
        return $qs->get($this->owner[$this->meta->foreignKey]);
    }
    
    protected function isFkPresent()
    {
        if ($this->owner[$this->meta->foreignKey] === null) return false;
        return true;
    }
}

?>
