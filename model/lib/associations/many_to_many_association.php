<?php

class SManyToManyAssociation extends SAssociationCollection
{
    public $assocForeignKey = Null;
    public $joinTable = Null;
    
    public function __construct($owner, $name, $class, $options = array())
    {
        parent::__construct($owner, $name, $class, $options);
        $this->assocForeignKey = $options['association_foreign_key'];
        $this->joinTable = $options['join_table'];
    }
    
    public function build($attributes = array())
    {
        $this->loadTarget();
        $class = $this->assocClass;
        $record = new $class($attributes);
        $this->target[] = $record;
        return $record;
    }
    
    public function beforeOwnerDestroy()
    {
        $sql = "DELETE FROM {$this->joinTable} WHERE "
        ."{$this->foreignKey} = '{$this->owner->id}'";
        SActiveRecord::connection()->execute($sql);
    }
    
    protected function findTarget()
    {
        return SActiveStore::findBySql($this->assocClass, $this->constructSql());
    }
    
    protected function insertRecord($record)
    {
        $record->save();
        // ajout d'un enregistrement dans la table de jointure ...
        $sql = "INSERT INTO {$this->joinTable} SET "
        ."{$this->assocForeignKey} = '{$record->id}', "
        ."{$this->foreignKey} = '{$this->owner->id}'";
        SActiveRecord::connection()->execute($sql);
    }
    
    protected function deleteRecord($record)
    {
        $sql = "DELETE FROM {$this->joinTable} WHERE "
        ."{$this->assocForeignKey} = '{$record->id}' AND "
        ."{$this->foreignKey} = '{$this->owner->id}'";
        SActiveRecord::connection()->execute($sql);
    }
    
    protected function countRecords($condition)
    {
        $this->loadTarget();
        return count($this->target);
    }
    
    private function constructSql()
    {
        $sql = "SELECT * FROM {$this->assocTableName} LEFT OUTER JOIN {$this->joinTable} "
        ."ON {$this->assocTableName}.{$this->assocPrimaryKey} "
        ."= {$this->joinTable}.{$this->assocForeignKey} "
        ."WHERE {$this->joinTable}.{$this->foreignKey} = '{$this->owner->id}'";
        return $sql;
    }
}

?>
