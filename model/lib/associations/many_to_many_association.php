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
        else $this->foreign_key = $owner_meta->underscored.'_id';
        if (isset($options['association_foreign_key'])) $this->assoc_foreign_key = $options['association_foreign_key'];
        else $this->assoc_foreign_key = SInflection::underscore($this->class).'_id';
        if (isset($options['join_table'])) $this->join_table = $options['join_table'];
        else $this->join_table = $this->join_table_name($owner_meta->class, $this->class);
        if (isset($options['scope'])) $this->scope = $options['scope'];
    }
    
    private function join_table_name($first_name, $second_name)
    {
        $first_name  = $this->undecorated_table_name($first_name);
        $second_name = $this->undecorated_table_name($second_name);
        
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
    
    private function undecorated_table_name($class_name)
    {
        return SInflection::pluralize(SInflection::underscore($class_name));
    }
}

class SManyToManyManager extends SManyAssociationManager
{
    public function before_owner_delete()
    {
        $this->connection()->execute("DELETE FROM {$this->meta->join_table} 
                                      WHERE {$this->meta->foreign_key} = '{$this->owner->id}'");
    }
    
    public function clear()
    {
        $this->connection()->execute("DELETE FROM {$this->meta->join_table} 
                                      WHERE {$this->meta->foreign_key} = '{$this->owner->id}'");
    }
    
    protected function insert_record($record)
    {
        if ($record->id === null) $record->save();
        $this->connection()->execute("INSERT INTO {$this->meta->join_table} 
                                      SET {$this->meta->assoc_foreign_key} = '{$record->id}', 
                                      {$this->meta->foreign_key} = '{$this->owner->id}'");
    }
    
    protected function delete_record($record)
    {
        $this->connection()->execute("DELETE FROM {$this->meta->join_table} 
                                      WHERE {$this->meta->assoc_foreign_key} = '{$record->id}' 
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
