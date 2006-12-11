<?php

class SHasManyMeta extends SAssociationMeta
{
    public $dependent = null;
    
    public $through_table_name  = null;
    public $through_foreign_key = null;
    public $source_assoc_type   = null;
    
    public function __construct($owner_meta, $assoc_name, $options)
    {
        parent::__construct($owner_meta, $assoc_name, $options);
        $this->assert_valid_options($options, array('dependent', 'through'));
        
        if (isset($options['through']))
        {
            $this->type = 'SHasManyThrough';
            $through_class = SInflection::camelize(SInflection::singularize($options['through']));
            $through_meta = SActiveRecordMeta::retrieve($through_class);;
            $this->through_table_name = $through_meta->table_name;
            $this->through_foreign_key = $owner_meta->underscored.'_id';
            
            if (isset($through_meta->relationships[$this->underscored]))
                $r = $through_meta->relationships[$this->underscored];
            elseif (isset($through_meta->relationships[SInflection::underscore(SInflection::pluralize($this->class))]))
                $r = $through_meta->relationships[SInflection::underscore(SInflection::pluralize($this->class))];
            
            if (is_array($r)) $this->source_assoc_type = $r['assoc_type'];
            else $this->source_assoc_type = $r;
            
            if ($this->source_assoc_type == 'belongs_to')
                $this->foreign_key = $owner_meta->underscored.'_id';
            elseif ($this->source_assoc_type == 'has_many')
                $this->foreign_key = $through_meta->underscored.'_id';
        }
        else
        {
            if (isset($options['foreign_key'])) $this->foreign_key = $options['foreign_key'];
            else $this->foreign_key = $owner_meta->underscored.'_id';
            
            if (isset($options['dependent']))
            {
                if (!in_array($options['dependent'], array('delete', 'delete_all', 'nullify')))
                    throw new SException("The 'dependent' option expects either 'delete', 'delete_all', or 'nullify'");
                
                $this->dependent = $options['dependent'];
            }
        }
    }
}

class SHasManyManager extends SManyAssociationManager
{
    public function before_owner_delete()
    {
        if ($this->meta->dependent === null) return;
        $this->clear();
    }
    
    public function clear()
    {
        switch ($this->meta->dependent)
        {
            case 'delete':
                foreach ($this->all() as $r) $r->delete();
                break;
            case 'delete_all':
                $this->connection()->execute("DELETE FROM {$this->meta->table_name} WHERE ".$this->get_sql_filter());
                break;
            case 'nullify':
                $this->connection()->execute("UPDATE {$this->meta->table_name} 
                                     SET {$this->meta->table_name}.{$this->meta->foreign_key} = NULL
                                     WHERE ".$this->get_sql_filter());
                break;
        }
    }
    
    protected function insert_record($record)
    {
        $fk = $this->meta->foreign_key;
        $record->$fk = $this->owner->id;
        $record->save();
    }
    
    protected function delete_record($record)
    {
        if ($this->meta->dependent == 'delete') $record->delete();
        else
        {
            $this->connection()->execute("UPDATE {$this->meta->table_name} 
                                         SET {$this->meta->table_name}.{$this->meta->foreign_key} = NULL
                                         WHERE ".$this->get_sql_filter()." 
                                         AND {$this->meta->table_name}.{$this->meta->identity_field} = '{$record->id}'");
        }
    }
    
    protected function get_query_set()
    {
        $qs = new SQuerySet($this->meta);
        return $qs->filter($this->get_sql_filter());
    }
    
    protected function get_sql_filter()
    {
        return "{$this->meta->table_name}.{$this->meta->foreign_key} = '{$this->owner->id}'";
    }
}

?>
