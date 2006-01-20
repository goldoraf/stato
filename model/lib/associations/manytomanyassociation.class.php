<?php

class ManyToManyAssociation extends AssociationCollection
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
        $entity = new $class($attributes);
        $this->target[] = $entity;
        return $entity;
    }
    
    public function beforeOwnerDestroy()
    {
        $sql = "DELETE FROM {$this->joinTable} WHERE "
        ."{$this->foreignKey} = '{$this->owner->id}'";
        $this->db->execute($sql);
    }
    
    protected function findTarget()
    {
        return ActiveStore::findBySql($this->assocClass, $this->constructSql());
    }
    
    protected function insertEntity($entity)
    {
        $entity->save();
        // ajout d'un enregistrement dans la table de jointure ...
        $sql = "INSERT INTO {$this->joinTable} SET "
        ."{$this->assocForeignKey} = '{$entity->id}', "
        ."{$this->foreignKey} = '{$this->owner->id}'";
        $this->db->execute($sql);
    }
    
    protected function deleteEntity($entity)
    {
        $sql = "DELETE FROM {$this->joinTable} WHERE "
        ."{$this->assocForeignKey} = '{$entity->id}' AND "
        ."{$this->foreignKey} = '{$this->owner->id}'";
        $this->db->execute($sql);
    }
    
    protected function countEntities($condition)
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
