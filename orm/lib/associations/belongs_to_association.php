<?php

class SBelongsToMeta extends SAssociationMeta
{
    public function __construct($owner_meta, $assoc_name, $options)
    {
        parent::__construct($owner_meta, $assoc_name, $options);
        $this->assert_valid_options($options);
        if (isset($options['foreign_key'])) $this->foreign_key = $options['foreign_key'];
        else $this->foreign_key = SInflection::underscore($this->class).'_id';
    }
}

class SBelongsToManager extends SAssociationManager
{
    public function replace($record)
    {
        if ($record === null)
        {
            $this->target = null;
            $this->owner[$this->meta->foreign_key] = null;
        }
        else
        {
            $this->check_record_type($record);
            $this->target = $record;
            if (!$record->is_new_record()) $this->owner[$this->meta->foreign_key] = $record->id;
        }
        $this->loaded = true;
    }
    
    public function before_owner_save()
    {
        if ($this->target !== null)
        {
            if ($this->target->is_new_record()) $this->target->save();
            $this->owner[$this->meta->foreign_key] = $this->target->id;
        }
    }
    
    protected function find_target()
    {
        if ($this->owner[$this->meta->foreign_key] === null) return null;
        $qs = new SQuerySet($this->meta);
        return $qs->get($this->owner[$this->meta->foreign_key]);
    }
    
    protected function is_fk_present()
    {
        if ($this->owner[$this->meta->foreign_key] === null) return false;
        return true;
    }
}

?>
