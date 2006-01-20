<?php

class HasManyAssociation extends AssociationCollection
{
    public function build($attributes = array())
    {
        $this->loadTarget();
        $class = $this->assocClass;
        $entity = new $class($attributes);
        if (!$this->owner->isNewRecord()) $entity[$this->foreignKey] = $this->owner->id;
        $this->target[] = $entity;
        return $entity;
    }
    
    protected function findTarget()
    {
        return ActiveStore::findAll($this->assocClass, $this->constructSql());
    }
    
    protected function insertEntity($entity)
    {
        $fk = $this->foreignKey;
        $entity->$fk = $this->owner->id;
        $entity->save();
    }
    
    protected function deleteEntity($entity)
    {
        // si 'dependent', on delete l'entity ... (to do)
        $sql = 'UPDATE '.$this->assocTableName.
                ' SET '.$this->foreignKey.' = \'NULL\''.
                ' WHERE '.$this->foreignKey.' = \''.$this->owner->id.'\''.
                ' AND '.$this->assocPrimaryKey.' = \''.$entity->id.'\'';
        $this->db->execute($sql);
    }
    
    protected function countEntities($condition)
    {
        return ActiveStore::count($this->assocClass, $this->constructSql($condition));
    }
    
    private function constructSql($condition = Null)
    {
        $sql = "{$this->assocTableName}.{$this->foreignKey} = '{$this->owner->id}'";
        if ($condition != Null) $sql.= " AND $condition";
        return $sql;
    }
}

?>
