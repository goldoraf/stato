<?php

class SBelongsToAssociation extends SAssociation
{
    public function replace($record)
    {
        if ($record === Null)
        {
            $this->target = Null;
            $this->owner[$this->foreignKey] = Null;
        }
        else
        {
            $this->checkRecordType($record);
            $this->target = $record;
            if (!$record->isNewRecord()) $this->owner[$this->foreignKey] = $record->id;
        }
        $this->loaded = true;
    }
    
    public function create($attributes=array())
    {
        $class = $this->assocClass;
        $record = new $class($attributes);
        $record->save();
        $this->replace($record);
        return $record;
    }
    
    public function build($attributes=array())
    {
        $class = $this->assocClass;
        $record = new $class($attributes);
        $this->replace($record);
        return $record;
    }
    
    public function beforeOwnerSave()
    {
        if ($this->target !== null)
        {
            if ($this->target->isNewRecord()) $this->target->save();
            $this->owner[$this->foreignKey] = $this->target->id;
        }
    }
    
    public function afterOwnerSave() {}
    
    public function beforeOwnerDelete() {}
    
    public function afterOwnerDelete() {}
    
    protected function reset()
    {
        $this->target = null;
        $this->loaded = false;
    }
    
    protected function findTarget()
    {
        return SActiveStore::findByPk($this->assocClass, $this->owner[$this->foreignKey]);
    }
    
    protected function isFkPresent()
    {
        if ($this->owner[$this->foreignKey] === null) return false;
        return true;
    }
    
    private function constructSql()
    {
        return "{$this->assocTableName}.{$this->assocPrimaryKey} = '{$this->owner[$this->foreignKey]}'";
    }
}

?>
