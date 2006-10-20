<?php

class SHasOneMeta extends SAssociationMeta
{
    public function __construct($ownerMeta, $assocName, $options)
    {
        parent::__construct($ownerMeta, $assocName, $options);
        $this->assertValidOptions($options);
        if (isset($options['foreign_key'])) $this->foreignKey = $options['foreign_key'];
        else $this->foreignKey = $ownerMeta->underscored.'_id';
    }
}

class SHasOneManager extends SAssociationManager
{
    protected $ownerNewBeforeSave = false;
    
    public function replace($record)
    {
        if ($this->target !== null)
        {
            $this->target[$this->meta->foreignKey] = null;
            $this->target->save(); // ou remplacer l'ensemble par $this->target->delete() ???
        }
        
        if ($record === null) $this->target = null;
        else
        {
            $this->checkRecordType($record);
            if (!$this->owner->isNewRecord()) $record[$this->meta->foreignKey] = $this->owner->id;
            $this->target = $record;
        }
        $this->loaded = true;
    }
    
    public function beforeOwnerSave()
    {
        if ($this->owner->isNewRecord() && $this->target !== null)
            $this->ownerNewBeforeSave = true;
    }
    
    public function afterOwnerSave()
    {
        if ($this->target !== null)
        {
            if ($this->ownerNewBeforeSave) $this->target[$this->meta->foreignKey] = $this->owner->id;
            $this->target->save();
        }
    }
    
    protected function findTarget()
    {
        try
        {
            $qs = new SQuerySet($this->meta);
            return $qs->get("{$this->meta->foreignKey} = '{$this->owner->id}'");
        }
        catch (SActiveRecordDoesNotExist $e)
        {
            return null;
        }
    }
}

?>
