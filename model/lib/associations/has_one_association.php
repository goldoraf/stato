<?php

class SHasOneMeta extends SAssociationMeta
{
    public $dependent = null;
    
    public function __construct($owner_meta, $assoc_name, $options)
    {
        parent::__construct($owner_meta, $assoc_name, $options);
        $this->assert_valid_options($options);
        if (isset($options['foreign_key'])) $this->foreign_key = $options['foreign_key'];
        else $this->foreign_key = $owner_meta->underscored.'_id';
        
        if (isset($options['dependent']))
        {
            if (!in_array($options['dependent'], array('delete', 'nullify')))
                throw new Exception("The 'dependent' option expects either 'delete' or 'nullify'");
            
            $this->dependent = $options['dependent'];
        }
    }
}

class SHasOneManager extends SAssociationManager
{
    protected $owner_new_before_save = false;
    
    public function replace($record)
    {
        if ($this->target() !== null)
        {
            if ($this->meta->dependent == 'delete') $this->target->delete();
            else
            {
                $this->target[$this->meta->foreign_key] = null;
                $this->target->save();
            }
        }
        
        if ($record === null) $this->target = null;
        else
        {
            $this->check_record_type($record);
            if (!$this->owner->is_new_record()) $record[$this->meta->foreign_key] = $this->owner->id;
            $this->target = $record;
        }
        $this->loaded = true;
    }
    
    public function before_owner_save()
    {
        if ($this->owner->is_new_record() && $this->target !== null)
            $this->owner_new_before_save = true;
    }
    
    public function after_owner_save()
    {
        if ($this->target !== null)
        {
            if ($this->owner_new_before_save) $this->target[$this->meta->foreign_key] = $this->owner->id;
            $this->target->save();
        }
    }
    
    public function before_owner_delete()
    {
        if ($this->meta->dependent === null || $this->target() === null) return;
        
        switch ($this->meta->dependent)
        {
            case 'delete':
                $this->target->delete();
                break;
            case 'nullify':
                $this->target[$this->meta->foreign_key] = null;
                $this->target->save();
                break;
        }
    }
    
    protected function find_target()
    {
        try
        {
            $qs = new SQuerySet($this->meta);
            return $qs->get("{$this->meta->foreign_key} = '{$this->owner->id}'");
        }
        catch (SActiveRecordDoesNotExist $e)
        {
            return null;
        }
    }
}

?>
