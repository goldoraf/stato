<?php

class SHasManyAssociation extends SAssociationCollection
{
    public function build($attributes = array())
    {
        $this->loadTarget();
        $class = $this->assocClass;
        $record = new $class($attributes);
        if (!$this->owner->isNewRecord()) $record[$this->foreignKey] = $this->owner->id;
        $this->target[] = $record;
        return $record;
    }
    
    protected function findTarget()
    {
        return SActiveStore::findAll($this->assocClass, $this->constructSql());
    }
    
    protected function insertRecord($record)
    {
        $fk = $this->foreignKey;
        $record->$fk = $this->owner->id;
        $record->save();
    }
    
    protected function deleteRecord($record)
    {
        // si 'dependent', on delete l'entity ... (to do)
        $sql = 'UPDATE '.$this->assocTableName.
                ' SET '.$this->foreignKey.' = \'NULL\''.
                ' WHERE '.$this->foreignKey.' = \''.$this->owner->id.'\''.
                ' AND '.$this->assocPrimaryKey.' = \''.$record->id.'\'';
        SDatabase::getInstance()->execute($sql);
    }
    
    protected function countRecords($condition)
    {
        return SActiveStore::count($this->assocClass, $this->constructSql($condition));
    }
    
    private function constructSql($condition = Null)
    {
        $sql = "{$this->assocTableName}.{$this->foreignKey} = '{$this->owner->id}'";
        if ($condition != Null) $sql.= " AND $condition";
        return $sql;
    }
}

?>
