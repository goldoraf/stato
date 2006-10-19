<?php

class SHasManyThroughException extends SException
{
    public function __construct()
    {
        parent::__construct('HasManyThrough associations only enables data retrieval. 
            You can\'t add or delete records.');
    }
}

class SHasManyThroughManager extends SHasManyManager
{
    protected function getQuerySet()
    {
        if ($this->sourceAssocType == 'belongs_to')
        {
            $assocPk = $this->meta->identityField;
            $sourcePk = $this->meta->foreignKey;
        }
        else
        {
            $sourcePk = $this->meta->identityField;
            $assocPk = $this->meta->foreignKey;
        }
        
        $qs = new SQuerySet($this->meta);
        return $qs->join("LEFT OUTER JOIN {$this->meta->throughTableName} 
                          ON {$this->meta->tableName}.{$assocPk} 
                          = {$this->meta->throughTableName}.{$sourcePk}")
                  ->filter($this->getSqlFilter());
    }
    
    protected function getSqlFilter()
    {
        return "{$this->meta->throughTableName}.{$this->meta->throughForeignKey} = '{$this->owner->id}'";
    }
    
    public function add($records)
    {
        throw new SHasManyThroughException();
    }
    
    public function delete($records)
    {
        throw new SHasManyThroughException();
    }
    
    public function clear()
    {
        throw new SHasManyThroughException();
    }
    
    protected function insertRecord($record) {}
    
    protected function deleteRecord($record) {}
}

?>
