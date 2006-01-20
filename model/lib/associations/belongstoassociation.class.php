<?php

class BelongsToAssociation extends Association
{
    public function replace($entity)
    {
        if ($entity === Null)
        {
            $this->target = Null;
            $this->owner[$this->foreignKey] = Null;
        }
        else
        {
            $this->checkEntityType($entity);
            $this->target = $entity;
            if (!$entity->isNewRecord()) $this->owner[$this->foreignKey] = $entity->id;
        }
        $this->loaded = true;
    }
    
    public function create($attributes=array())
    {
        $class = $this->assocClass;
        $entity = new $class($attributes);
        $entity->save();
        $this->replace($entity);
        return $entity;
    }
    
    public function build($attributes=array())
    {
        $class = $this->assocClass;
        $entity = new $class($attributes);
        $this->replace($entity);
        return $entity;
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
        return ActiveStore::findByPk($this->assocClass, $this->owner[$this->foreignKey]);
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
