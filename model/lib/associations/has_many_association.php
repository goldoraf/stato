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
    
    public function beforeOwnerDelete()
    {
        if (!isset($this->options['dependent'])) return;
        
        switch ($this->options['dependent'])
        {
            case 'delete':
                $this->loadTarget();
                foreach ($this->target as $r) $r->delete();
                break;
            case 'delete_all':
                SActiveStore::deleteAll($this->assocClass, $this->constructSql());
                break;
            case 'nullify':
                SActiveStore::updateAll($this->assocClass, 
                                        "{$this->assocTableName}.{$this->foreignKey} = NULL",
                                        $this->constructSql());
                break;
            default:
                throw new SException("The 'dependent' option expects either 'delete', 'delete_all', or 'nullify'");
        }
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
        if (isset($this->options['dependent'])) $r->delete();
        else
        {
            SActiveStore::updateAll($this->assocClass, 
                                    "{$this->assocTableName}.{$this->foreignKey} = NULL",
                                    $this->constructSql("{$this->assocTableName}.{$this->assocPrimaryKey} = '{$record->id}'"));
        }
    }
    
    protected function countRecords($condition)
    {
        return SActiveStore::count($this->assocClass, $this->constructSql($condition));
    }
    
    protected function constructSql($condition = Null)
    {
        $sql = "{$this->assocTableName}.{$this->foreignKey} = '{$this->owner->id}'";
        if ($condition != Null) $sql.= " AND $condition";
        return $sql;
    }
}

?>
