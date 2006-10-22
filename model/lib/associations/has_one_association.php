<?php

class SHasOneMeta extends SAssociationMeta
{
    public $dependent = null;
    
    public function __construct($ownerMeta, $assocName, $options)
    {
        parent::__construct($ownerMeta, $assocName, $options);
        $this->assertValidOptions($options);
        if (isset($options['foreign_key'])) $this->foreignKey = $options['foreign_key'];
        else $this->foreignKey = $ownerMeta->underscored.'_id';
        
        if (isset($options['dependent']))
        {
            if (!in_array($options['dependent'], array('delete', 'nullify')))
                throw new SException("The 'dependent' option expects either 'delete' or 'nullify'");
            
            $this->dependent = $options['dependent'];
        }
    }
}

class SHasOneManager extends SAssociationManager
{
    protected $ownerNewBeforeSave = false;
    
    public function replace($record)
    {
        if ($this->target() !== null)
        {
            if ($this->meta->dependent == 'delete') $this->target->delete();
            else
            {
                $this->target[$this->meta->foreignKey] = null;
                $this->target->save();
            }
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
    
    public function beforeOwnerDelete()
    {
        if ($this->meta->dependent === null || $this->target() === null) return;
        
        switch ($this->meta->dependent)
        {
            case 'delete':
                $this->target->delete();
                break;
            case 'nullify':
                $this->target[$this->meta->foreignKey] = null;
                $this->target->save();
                break;
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
