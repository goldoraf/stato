<?php

class SHasOneAssociation extends SBelongsToAssociation
{
    protected $ownerNewBeforeSave = false;
    
    public function replace($record)
    {
        if ($this->target !== Null)
        {
            $this->target[$this->foreignKey] = Null;
            $this->target->save(); // ou remplacer l'ensemble par $this->target->delete() ???
        }
        if ($record === Null)
        {
            $this->target = Null;
        }
        else
        {
            $this->checkRecordType($record);
            if (!$this->owner->isNewRecord()) $record[$this->foreignKey] = $this->owner->id;
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
            if ($this->ownerNewBeforeSave) $this->target[$this->foreignKey] = $this->owner->id;
            $this->target->save();
        }
    }
    
    public function beforeOwnerDelete() {}
    
    public function afterOwnerDelete() {}
    
    protected function findTarget()
    {
        return SActiveStore::findFirst($this->assocClass, $this->constructSql());
    }
    
    private function constructSql()
    {
        return "{$this->assocTableName}.{$this->foreignKey} = '{$this->owner->readId()}'";
    }
}

?>
