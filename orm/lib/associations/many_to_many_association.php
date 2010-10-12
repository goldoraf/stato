<?php

class SManyToManyMeta extends SAssociationMeta
{
    public $assoc_foreign_key = null;
    public $join_table = null;
    public $scope = null;
    
    public function __construct($owner_meta, $assoc_name, $options)
    {
        parent::__construct($owner_meta, $assoc_name, $options);
        $this->assert_valid_options($options, array('association_foreign_key', 'join_table', 'scope'));
        if (isset($options['foreign_key'])) $this->foreign_key = $options['foreign_key'];
        else $this->foreign_key = $owner_meta->get_possible_fk();
        if (isset($options['association_foreign_key'])) $this->assoc_foreign_key = $options['association_foreign_key'];
        else $this->assoc_foreign_key = $this->base_meta()->get_possible_fk();
        if (isset($options['join_table'])) $this->join_table = $options['join_table'];
        else $this->join_table = $this->join_table_name($owner_meta, $this->base_meta());
        if (isset($options['scope'])) $this->scope = $options['scope'];
    }
    
    private function join_table_name($first_meta, $second_meta)
    {
        $first_name  = $first_meta->table_name;
        $second_name = $second_meta->table_name;
        
        if ($first_name < $second_name)
            $table_name = "${first_name}_${second_name}";
        else
            $table_name = "${second_name}_${first_name}";
            
        if (SActiveRecord::$table_name_prefix !== null)
            $table_name = SActiveRecord::$table_name_prefix.'_'.$table_name;
        if (SActiveRecord::$table_name_suffix !== null)
            $table_name.= '_'.SActiveRecord::$table_name_suffix;
            
        return $table_name;
    }
}

class SManyToManyManager extends SManyAssociationManager
{
    public function before_owner_delete()
    {
        $this->clear();
    }
    
    public function clear()
    {
        if ($this->meta->scope === null)
            $this->connection()->execute("DELETE FROM {$this->meta->join_table} 
                                          WHERE {$this->meta->foreign_key} = '{$this->owner->id}'");
        else
        {
            $quoted_record_ids = array();
            foreach ($this->all() as $record) $quoted_record_ids[] = $this->connection()->quote($record->id);
            if (count($quoted_record_ids) == 0) return;
            $this->connection()->execute("DELETE FROM {$this->meta->join_table} 
                                          WHERE {$this->meta->foreign_key} = '{$this->owner->id}' 
                                          AND {$this->meta->assoc_foreign_key} IN (".implode(',', $quoted_record_ids).")");
        }
    }
    
    public function add_by_ids($ids)
    {
        $ids = array_diff($ids, $this->ids());
        foreach ($ids as $id) $this->insert_record_by_id($id);
    }
    
    public function delete_by_ids($ids)
    {
        $ids = array_intersect($ids, $this->ids());
        foreach ($ids as $id) $this->delete_record_by_id($id);
    }
    
    protected function insert_record($record)
    {
        if ($record->id === null) $record->save();
        $this->insert_record_by_id($record->id);
    }
    
    protected function insert_record_by_id($record_id)
    {
        $this->connection()->execute("INSERT INTO {$this->meta->join_table} 
                                      SET {$this->meta->assoc_foreign_key} = '{$record_id}', 
                                      {$this->meta->foreign_key} = '{$this->owner->id}'");
    }
    
    protected function delete_record($record)
    {
        $this->delete_record_by_id($record->id);
    }
    
    protected function delete_record_by_id($record_id)
    {
        $this->connection()->execute("DELETE FROM {$this->meta->join_table} 
                                      WHERE {$this->meta->assoc_foreign_key} = '{$record_id}' 
                                      AND {$this->meta->foreign_key} = '{$this->owner->id}'");
    }
    
    protected function get_query_set()
    {
        $qs = new SQuerySet($this->meta);
        return $qs->join("LEFT OUTER JOIN {$this->meta->join_table} 
                          ON {$this->meta->table_name}.{$this->meta->identity_field} 
                          = {$this->meta->join_table}.{$this->meta->assoc_foreign_key}")
                  ->filter($this->get_sql_filter());
    }
    
    protected function get_sql_filter()
    {
        $sql = "{$this->meta->join_table}.{$this->meta->foreign_key} = '{$this->owner->id}'";
        if ($this->meta->scope !== null) $sql.= ' AND '.$this->meta->scope;
        return $sql;
    }
}

?>
