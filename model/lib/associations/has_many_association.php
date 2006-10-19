<?php

class SHasManyMeta extends SAssociationMeta
{
    public $dependent = null;
    
    public $throughTableName  = null;
    public $throughForeignKey = null;
    public $sourceAssocType   = null;
    
    public function __construct($ownerMeta, $assocName, $options)
    {
        parent::__construct($ownerMeta, $assocName, $options);
        $this->assertValidOptions($options, array('dependent', 'through'));
        
        if (isset($options['through']))
        {
            $this->type = 'SHasManyThrough';
            $throughClass = SInflection::camelize(SInflection::singularize($options['through']));
            $throughMeta = SActiveRecordMeta::retrieve($throughClass);;
            $this->throughTableName = $throughMeta->tableName;
            $this->throughForeignKey = $ownerMeta->underscored.'_id';
            
            if (isset($throughMeta->relationships[$this->underscored]))
                $r = $throughMeta->relationships[$this->underscored];
            elseif (isset($throughMeta->relationships[SInflection::underscore(SInflection::pluralize($this->class))]))
                $r = $throughMeta->relationships[SInflection::underscore(SInflection::pluralize($this->class))];
            
            if (is_array($r)) $this->sourceAssocType = $r['assoc_type'];
            else $this->sourceAssocType = $r;
            
            if ($this->sourceAssocType == 'belongs_to')
                $this->foreignKey = $ownerMeta->underscored.'_id';
            elseif ($this->sourceAssocType == 'has_many')
                $this->foreignKey = $throughMeta->underscored.'_id';
        }
        else
        {
            if (isset($options['foreign_key'])) $this->foreignKey = $options['foreign_key'];
            else $this->foreignKey = $ownerMeta->underscored.'_id';
            
            if (isset($options['dependent'])) $this->dependent = $options['dependent'];
        }
    }
}

class SHasManyManager extends SManyAssociationManager
{
    public function beforeOwnerDelete()
    {
        if ($this->meta->dependent === null) return;
        
        switch ($this->meta->dependent)
        {
            case 'delete':
                foreach ($this->all() as $r) $r->delete();
                break;
            case 'delete_all':
                $this->connection()->execute("DELETE FROM {$this->meta->tableName} WHERE ".$this->getSqlFilter());
                break;
            case 'nullify':
                $this->clear();
                break;
            default:
                throw new SException("The 'dependent' option expects either 'delete', 'delete_all', or 'nullify'");
        }
    }
    
    public function clear()
    {
        $this->connection()->execute("UPDATE {$this->meta->tableName} 
                                     SET {$this->meta->tableName}.{$this->meta->foreignKey} = NULL
                                     WHERE ".$this->getSqlFilter());
    }
    
    protected function insertRecord($record)
    {
        $fk = $this->meta->foreignKey;
        $record->$fk = $this->owner->id;
        $record->save();
    }
    
    protected function deleteRecord($record)
    {
        if ($this->meta->dependent == 'delete') $record->delete();
        else
        {
            $this->connection()->execute("UPDATE {$this->meta->tableName} 
                                         SET {$this->meta->tableName}.{$this->meta->foreignKey} = NULL
                                         WHERE ".$this->getSqlFilter()." 
                                         AND {$this->meta->tableName}.{$this->meta->identityField} = '{$record->id}'");
        }
    }
    
    protected function getQuerySet()
    {
        $qs = new SQuerySet($this->meta);
        return $qs->filter($this->getSqlFilter());
    }
    
    protected function getSqlFilter()
    {
        return "{$this->meta->tableName}.{$this->meta->foreignKey} = '{$this->owner->id}'";
    }
}

?>
