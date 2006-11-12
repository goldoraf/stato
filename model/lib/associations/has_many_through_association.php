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
    protected function get_query_set()
    {
        if ($this->source_assoc_type == 'belongs_to')
        {
            $assoc_pk = $this->meta->identity_field;
            $source_pk = $this->meta->foreign_key;
        }
        else
        {
            $source_pk = $this->meta->identity_field;
            $assoc_pk = $this->meta->foreign_key;
        }
        
        $qs = new SQuerySet($this->meta);
        return $qs->join("LEFT OUTER JOIN {$this->meta->through_table_name} 
                          ON {$this->meta->table_name}.{$assoc_pk} 
                          = {$this->meta->through_table_name}.{$source_pk}")
                  ->filter($this->get_sql_filter());
    }
    
    protected function get_sql_filter()
    {
        return "{$this->meta->through_table_name}.{$this->meta->through_foreign_key} = '{$this->owner->id}'";
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
    
    protected function insert_record($record) {}
    
    protected function delete_record($record) {}
}

?>
