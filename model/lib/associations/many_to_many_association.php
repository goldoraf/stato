<?php

class SManyToManyMeta extends SAssociationMeta
{
    public $assocForeignKey = null;
    public $joinTable = null;
    
    public function __construct($ownerMeta, $assocName, $options)
    {
        parent::__construct($ownerMeta, $assocName, $options);
        $this->assertValidOptions($options, array('association_foreign_key', 'join_table'));
        if (isset($options['foreign_key'])) $this->foreignKey = $options['foreign_key'];
        else $this->foreignKey = $ownerMeta->underscored.'_id';
        if (isset($options['association_foreign_key'])) $this->assocForeignKey = $options['association_foreign_key'];
        else $this->assocForeignKey = SInflection::underscore($this->class).'_id';
        if (isset($options['join_table'])) $this->joinTable = $options['join_table'];
        else $this->joinTable = $this->joinTableName($ownerMeta->class, $this->class);
    }
    
    private function joinTableName($firstName, $secondName)
    {
        $firstName  = $this->undecoratedTableName($firstName);
        $secondName = $this->undecoratedTableName($secondName);
        
        if ($firstName < $secondName)
            $tableName = "${firstName}_${secondName}";
        else
            $tableName = "${secondName}_${firstName}";
            
        if (SActiveRecord::$tableNamePrefix !== null)
            $tableName = SActiveRecord::$tableNamePrefix.'_'.$tableName;
        if (SActiveRecord::$tableNameSuffix !== null)
            $tableName.= '_'.SActiveRecord::$tableNameSuffix;
            
        return $tableName;
    }
    
    private function undecoratedTableName($className)
    {
        return SInflection::pluralize(SInflection::underscore($className));
    }
}

class SManyToManyManager extends SManyAssociationManager
{
    public function beforeOwnerDelete()
    {
        $this->connection()->execute("DELETE FROM {$this->meta->joinTable} 
                                      WHERE {$this->meta->foreignKey} = '{$this->owner->id}'");
    }
    
    public function clear()
    {
        $this->connection()->execute("DELETE FROM {$this->meta->joinTable} 
                                      WHERE {$this->meta->foreignKey} = '{$this->owner->id}'");
    }
    
    protected function insertRecord($record)
    {
        if ($record->id === null) $record->save();
        $this->connection()->execute("INSERT INTO {$this->meta->joinTable} 
                                      SET {$this->meta->assocForeignKey} = '{$record->id}', 
                                      {$this->meta->foreignKey} = '{$this->owner->id}'");
    }
    
    protected function deleteRecord($record)
    {
        $this->connection()->execute("DELETE FROM {$this->meta->joinTable} 
                                      WHERE {$this->meta->assocForeignKey} = '{$record->id}' 
                                      AND {$this->meta->foreignKey} = '{$this->owner->id}'");
    }
    
    protected function getQuerySet()
    {
        $qs = new SQuerySet($this->meta);
        return $qs->join("LEFT OUTER JOIN {$this->meta->joinTable} 
                          ON {$this->meta->tableName}.{$this->meta->identityField} 
                          = {$this->meta->joinTable}.{$this->meta->assocForeignKey}")
                  ->filter($this->getSqlFilter());
    }
    
    protected function getSqlFilter()
    {
        return "{$this->meta->joinTable}.{$this->meta->foreignKey} = '{$this->owner->id}'";
    }
}

?>
