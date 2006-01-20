<?php

class OneToOneAssociation extends BelongsToAssociation
{
    public $assocForeignKey = Null;
    
    protected $ownerNewBeforeSave = false;
    
    public function __construct($owner, $name, $class, $options = array())
    {
        parent::__construct($owner, $name, $class, $options);
        $this->assocForeignKey = $options['association_foreign_key'];
    }
    
    public function replace($entity)
    {
        if ($this->target !== Null)
        {
            $this->target[$this->foreignKey] = Null;
            $this->target->save(); // ou remplacer l'ensemble par $this->target->delete() ???
        }
        if ($entity === Null)
        {
            $this->target = Null;
            $this->owner[$this->assocForeignKey] = Null;
        }
        else
        {
            $this->checkEntityType($entity);
            if (!$entity->isNewRecord()) $this->owner[$this->assocForeignKey] = $entity->id;
            if (!$this->owner->isNewRecord()) $entity[$this->foreignKey] = $this->owner->id;
            $this->target = $entity;
        }
        $this->loaded = true;
    }
    
    public function beforeOwnerSave()
    {
        if ($this->target !== null)
        {
            if ($this->target->isNewRecord()) $this->target->save();
            $this->owner[$this->assocForeignKey] = $this->target->id;
            if ($this->owner->isNewRecord()) $this->ownerNewBeforeSave = true;
        }
    }
    
    public function afterOwnerSave()
    {
        if ($this->ownerNewBeforeSave && $this->target !== null)
        {
            $this->target[$this->foreignKey] = $this->owner->id;
            $this->target->save();
        }
    }
    
    public function beforeOwnerDelete() {}
    
    public function afterOwnerDelete() {}
    
    protected function findTarget()
    {
        return ActiveStore::findFirst($this->assocClass, $this->constructSql());
    }
    
    protected function isFkPresent()
    {
        if ($this->owner[$this->assocForeignKey] === null) return false;
        return true;
    }
    
    private function constructSql()
    {
        return "{$this->assocTableName}.{$this->foreignKey} = '{$this->owner->readId()}'";
    }
}

?>
