<?php

class SBelongsToMeta extends SAssociationMeta
{
    public $dependent = null;
    
    public function __construct($owner_meta, $assoc_name, $options)
    {
        parent::__construct($owner_meta, $assoc_name, $options);
        $this->assert_valid_options($options, array('dependent'));
        if (isset($options['foreign_key'])) $this->foreign_key = $options['foreign_key'];
        else $this->foreign_key = $this->base_meta()->get_possible_fk();
        
        if (isset($options['dependent']))
        {
            if (!in_array($options['dependent'], array('delete', 'destroy')))
                throw new Exception("The 'dependent' option expects either 'delete' or 'destroy'");
            
            $this->dependent = $options['dependent'];
        }
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
    
    public function before_owner_delete()
    {
        if ($this->meta->dependent === null || $this->target() === null) return;
        
        switch ($this->meta->dependent)
        {
            case 'delete':
                $this->target->delete();
                break;
            case 'destroy':
                $this->target->destroy();
                break;
        }
    }
    
    protected function find_target()
    {
        if (!$this->is_fk_present()) return null;
        $qs = new SQuerySet($this->meta);
        return $qs->get($this->owner[$this->meta->foreign_key]);
    }
    
    protected function is_fk_present()
    {
        if ($this->owner[$this->meta->foreign_key] == null) return false;
        return true;
    }
}

?>
