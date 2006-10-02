<?php

class SHasManyThroughAssociation extends SHasManyAssociation
{
    protected function findTarget()
    {
        return SActiveStore::findBySql($this->assocClass, 
            implode(' ', array($this->constructSelect(), $this->constructFrom(), 
                               $this->constructJoins(), $this->constructConditions())));
    }
    
    protected function constructSelect()
    {
        return 'SELECT '.$this->assocTableName.'.*';
    }
    
    protected function constructFrom()
    {
        return 'FROM '.$this->assocTableName;
    }
    
    protected function constructJoins()
    {
        return 'INNER JOIN '.$this->options['through_table_name']
        ." ON {$this->assocTableName}.{$this->assocPrimaryKey}"
        .' = '.$this->options['through_table_name'].".{$this->foreignKey}";
    }
    
    protected function constructConditions($condition = Null)
    {
        $sql = 'WHERE '.$this->options['through_table_name'].'.'.$this->options['through_foreign_key']." = '{$this->owner->id}'";
        if ($condition != Null) $sql.= " AND $condition";
        return $sql;
    }
}

?>
