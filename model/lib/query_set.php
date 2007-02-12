<?php

class SActiveRecordDoesNotExist extends Exception {}

class SQuerySet implements Iterator, Countable
{
    public $filters  = array();
    public $excludes = array();
    public $includes = array();
    public $params   = array();
    public $order_by = array();
    public $joins    = array();
    public $offset   = null;
    public $limit    = null;
    public $distinct = false;
    
    protected $resource = null;
    protected $count    = 0;
    protected $cache    = null;
    protected $meta     = null;
    protected $conn     = null;
    
    protected $pk_lookup   = array();
    protected $schema_abbr = array();
    
    public function __construct($meta)
    {
        $this->meta = $meta;
        $this->conn = SActiveRecord::connection();
    }
    
    public function __clone()
    {
        $this->resource = null;
        $this->count    = 0;
        $this->cache    = null;
    }
    
    /**
     * Iterator methods
     **/
    public function current()
    {
        return $this->cache[$this->count];
    }

    public function key()
    {
        return $this->count;
    }

    public function next()
    {
        $this->count++;
    }

    public function rewind()
    {
        $this->count = 0;
    }

    public function valid()
    {
        if ($this->resource === null && is_array($this->cache)) 
            return isset($this->cache[$this->count]);
        elseif ($this->resource === null && $this->cache === null)
        {
            $this->resource = $this->conn->select($this->prepare_select());
            $this->cache = array();
            if (!empty($this->includes))
            {
                $this->fetch_all_with_assocs();
                return !empty($this->cache);
            }
        }
        return $this->fetch();
    }
    
    public function first()
    {
        $this->rewind();
        if ($this->valid()) return $this->current();
        else return null;
    }
    
    public function to_array()
    {
        if (!is_array($this->cache))
        {
            $this->resource = $this->conn->select($this->prepare_select());
            if (!empty($this->includes)) $this->fetch_all_with_assocs();
            else { while ($this->fetch()) { } }
        }
        return $this->cache;
    }
    
    public function get()
    {
        $numargs = func_num_args();
        $args = func_get_args();
        if ($numargs == 1)
        {
            $pk = $this->meta->identity_field;
            if (!empty($this->includes)) $pk = $this->meta->table_name.'.'.$pk;
            
            if (is_object($args[0]) && get_class($args[0]) == $this->meta->class)
            {
                $values = array();
                foreach ($args[0]->assigned_values() as $k => $v)
                {
                    if ($v !== null)
                    {
                        $args[]   = $k.' = ?';
                        $values[] = $v;
                    }
                }
                $args[] = $values;
            }
            elseif (is_array($args[0])) 
                $args[] = $pk.' IN ('.join(',', $args[0]).')';
            elseif (is_numeric($args[0]))
                $args[] = $pk.' = \''.$args[0].'\'';
            else 
                $args[] = $args[0];
            unset($args[0]);
        }
        $clone = call_user_func_array(array($this, 'filter'), $args);
        $set = $clone->to_array();
        if (($count = count($set)) < 1) throw new SActiveRecordDoesNotExist();
        elseif ($count == 1) return $set[0];
        else
        {
            $new_set = array();
            foreach ($set as $o) $new_set[$o->id] = $o;
            return $new_set;
        }
    }
    
    public function count()
    {
        if ($this->resource !== null) return $this->conn->row_count($this->resource);
        elseif ($this->resource === null && is_array($this->cache)) return count($this->cache);
        else
        {
            $clone = clone $this;
            $clone->order_by = array();
            $clone->includes = array();
            $clone->offset   = null;
            $clone->limit    = null;
            
            $rs = $this->conn->select($clone->prepare_select('COUNT(*)'));
            $row = $this->conn->fetch($rs, false);
            return $row[0];
        }
    }
    
    public function delete()
    {
        $clone = clone $this;
        $clone->includes = array();
        $clone->joins    = array();
        $clone->order_by = array();
        $this->conn->execute("DELETE FROM {$this->meta->table_name} ".$clone->sql_clause());
        return;
    }
    
    public function filter()
    {
        return $this->filter_or_exclude(func_get_args());
    }
    
    public function exclude()
    {
        return $this->filter_or_exclude(func_get_args(), true);
    }
    
    public function limit($limit, $offset = 0)
    {
        $clone = clone $this;
        $clone->limit = $limit;
        $clone->offset = $offset;
        return $clone;
    }
    
    public function order_by()
    {
        $args = func_get_args();
        $clone = clone $this;
        $clone->order_by = array_merge($this->order_by, $args);
        return $clone;
    }
    
    public function join($sql)
    {
        $clone = clone $this;
        $clone->joins[] = $sql;
        return $clone;
    }
    
    public function distinct()
    {
        $clone = clone $this;
        $clone->distinct = true;
        return $clone;
    }
    
    public function includes()
    {
        $args = func_get_args();
        $clone = clone $this;
        $clone->includes = array_merge($this->includes, $args);
        return $clone;
    }
    
    public function values()
    {
        $args = func_get_args();
        $v = new SValuesQuerySet($this->meta);
        $v->filters  = $this->filters;
        $v->excludes = $this->excludes;
        $v->params   = $this->params;
        $v->order_by = $this->order_by;
        $v->limit    = $this->limit;
        $v->offset   = $this->offset;
        $v->distinct = $this->distinct;
        if (!empty($args)) $v->fields = $args;
        return $v;
    }
    
    public function sql_clause()
    {
        $components = array();
        $components[] = $this->sql_joins();
        $components[] = $this->sql_conditions();
        $components[] = $this->sql_order_by();
        $components[] = $this->sql_limit();
        foreach ($components as $k => $v) if ($v === null) unset($components[$k]);
        return $this->sanitize_sql(implode(' ', $components));
    }
    
    public function prepare_select($fields = null)
    {
        if ($fields === null)
        {
            if (empty($this->includes)) $fields = '*';
            else $fields = implode(', ', $this->column_aliases());
        }
        $select = ($this->distinct) ? 'SELECT DISTINCT' : 'SELECT';
        return implode(' ', array($select, $fields, "FROM {$this->meta->table_name}", $this->sql_clause()));
    }
    
    protected function fetch()
    {
        $class = $this->meta->class;
        $row = $this->conn->fetch($this->resource);
        if (!$row)
        {
            $this->conn->free_result($this->resource);
            $this->resource = null;
            return false;
        }
        $this->cache[] = $this->fetch_row($row);
        return true;
    }
    
    protected function fetch_row($row)
    {
        if (in_array($this->meta->inheritance_field, array_keys($this->meta->attributes))
            && class_exists($row[$this->meta->inheritance_field], false)) $class = $row[$this->meta->inheritance_field];
        else $class = $this->meta->class;
        return new $class($row);
    }
    
    protected function filter_or_exclude($args, $exclude = false)
    {
        $numargs = count($args);
        $clone = clone $this;
        
        if ($numargs > 1 && is_array($args[$numargs - 1]))
        {
            $clone->params = array_merge($clone->params, $args[$numargs - 1]);
            unset($args[$numargs - 1]);
        }
        
        foreach ($args as $k => $arg)
        {
            if (preg_match('/^([a-zA-Z_]+)\->([a-zA-Z_]+)(.*)/', $arg, $matches))
            {
                if (!isset($this->meta->attributes[$matches[1]]))
                    throw new Exception('Association '.$matches[1].' does not exist.');
                
                $assoc_meta = $this->meta->attributes[$matches[1]]->meta;
                $field = $matches[2];
                $cond  = $matches[3];
                
                $clone->includes[] = $matches[1];
                $clone->filters[]  = "{$assoc_meta->table_name}.{$field}{$cond}";
                
                unset($args[$k]);
            }
        }
        
        if (count($args) == 0) return $clone;
        elseif (count($args) == 1)
        {
            $condition = array_pop($args);
            if ($exclude) $condition = "NOT ($condition)";
        }
        else
        {
            $condition = '('.implode(' AND ', $args).')';
            if ($exclude) $condition = 'NOT '.$condition;
        }
        $clone->filters[] = $condition;
        
        return $clone;
    }
    
    protected function sql_joins()
    {  
        if (empty($this->joins) && empty($this->includes)) return null;
        return implode(' ', array_merge($this->joins, $this->include_joins($this->includes)));
    }
    
    protected function sql_conditions()
    {
        if ($this->meta->descends_from() != 'SActiveRecord') $this->filters[] = $this->type_condition();
        if (empty($this->filters)) return null;
        return 'WHERE '.implode(' AND ', $this->filters);
    }
    
    protected function sql_limit()
    {
        if ($this->limit === null) return null;
        return $this->conn->limit($this->limit, $this->offset);
    }
    
    protected function sql_order_by()
    {
        if (empty($this->order_by)) return null;
        $orders = array();
        foreach ($this->order_by as $o)
        {
            if (strpos($o, '.') !== false) list($table, $o) = explode('.', $o);
            if ($o{0} == '-') $order = substr($o, 1).' DESC';
            else $order = "$o ASC";
            if (!empty($table)) $order = $table.'.'.$order;
            $orders[] = $order;
        }
        return 'ORDER BY '.implode(', ', $orders);
    }
    
    protected function type_condition()
    {
        return $this->meta->inheritance_field.' = \''.strtolower($this->meta->class).'\'';
    }
    
    protected function fetch_all_with_assocs()
    {
        $pk = $this->meta->table_name.'_'.$this->meta->identity_field;
        $class = $this->meta->class;
        $records = array();
        $records_in_order = array();
        $records_many_assocs = array();
        $many_assocs = array();
        
        foreach ($this->includes as $k)
        {
            $assoc_meta = $this->meta->attributes[$k]->meta;
            if ($assoc_meta->type == 'SHasMany' || $assoc_meta->type == 'SManyToMany') $many_assocs[] = $k;
        }
        
        while($row = $this->conn->fetch($this->resource))
        {
            $id = $row[$pk];
            if (!isset($records[$id]))
                $records_in_order[] = $records[$id] = new $class($this->extract_record($this->meta->table_name, $row));
            
            foreach ($this->includes as $k)
            {
                $assoc_meta = $this->meta->attributes[$k]->meta;
                
                if (isset($row[$this->pk_lookup[$assoc_meta->table_name]]))
                {
                    $record = $this->extract_record($assoc_meta->table_name, $row);
                    if ($record)
                    {
                        $assoc_class = $assoc_meta->class;
                        $assoc = new $assoc_class($record);
                        if (in_array($k, $many_assocs))
                        {
                            if (!isset($records_many_assocs[$id][$k][$assoc->id])) 
                                $records_many_assocs[$id][$k][$assoc->id] = $assoc;
                        } 
                        else $records[$id]->$k->set_target($assoc);
                    }
                }
            }
        }
        
        foreach ($records as $id => $record)
        {
            foreach ($many_assocs as $k)
            {
                if (isset($records_many_assocs[$id][$k]))
                    $records[$id]->$k->set_query_set(new SFilledQuerySet($this->meta->attributes[$k]->meta, array_values($records_many_assocs[$id][$k])));
                else
                    $records[$id]->$k->set_query_set(new SFilledQuerySet($this->meta->attributes[$k]->meta, array()));
            }
        }
                
        $this->conn->free_result($this->resource);
        $this->resource = null;
        $this->cache = $records_in_order;
    }
    
    // n'instancie pas les records dont ttes les values sont NULL !!!
    protected function extract_record($table_name, $row)
    {
        $record = array();
        $valid = false;
        foreach($row as $key => $value)
        {
            list($prefix, $column) = $this->schema_abbr[$key];
            if ($prefix == $table_name)
            {
                $record[$column] = $value;
                if ($value != null) $valid = true;
            }
        }
        if ($valid == true) return $record;
        return false;
    }
    
    protected function column_aliases()
    {
        $aliases = array();
        $tables = array($this->meta->table_name);
        foreach ($this->includes as $r) $tables[$r] = $this->meta->attributes[$r]->meta->table_name;
        foreach ($tables as $r => $t)
        {
            $columns = array_keys($this->conn->columns($t));
            
            foreach($columns as $column)
            {
                $abbr = $t.'_'.$column;
                $this->schema_abbr[$abbr] = array($t, $column);
                $aliases[] = join(array($t, $column), '.').' AS '.$abbr;
                if (is_string($r) && $column == $this->meta->attributes[$r]->meta->identity_field) $this->pk_lookup[$t] = $abbr;
            }
        }
        return $aliases;
    }
    
    protected function include_joins($includes)
    {
        $joins = array();
        
        foreach ($includes as $r)
            $joins[] = $this->association_join($this->meta->attributes[$r]->meta);
            
        return $joins;
    }
    
    protected function association_join($assoc_meta)
    {
        switch($assoc_meta->type)
        {
            case 'SBelongsTo':
                return "LEFT OUTER JOIN {$assoc_meta->table_name} ON "
                ."{$assoc_meta->table_name}.{$assoc_meta->identity_field} = "
                ."{$this->meta->table_name}.{$assoc_meta->foreign_key}";
                break;
                
            case 'SManyToMany':
                return "LEFT OUTER JOIN {$assoc_meta->join_table} ON "
                ."{$assoc_meta->join_table}.{$assoc_meta->foreign_key} = "
                ."{$this->meta->table_name}.{$this->meta->identity_field} "
                ."LEFT OUTER JOIN {$assoc_meta->table_name} ON "
                ."{$assoc_meta->join_table}.{$assoc_meta->assoc_foreign_key} = "
                ."{$assoc_meta->table_name}.{$assoc_meta->identity_field}";
                break;
                
            default:
                return "LEFT OUTER JOIN {$assoc_meta->table_name} ON "
                ."{$assoc_meta->table_name}.{$assoc_meta->foreign_key} = "
                ."{$this->meta->table_name}.{$this->meta->identity_field}";
        }
    }
    
    protected function sanitize_sql($stmt)
    {
        if (!empty($this->params))
        {
            if (strpos($stmt, ':')) return $this->replace_named_bind_variables($stmt, $this->params);
            elseif (strpos($stmt, '?')) return $this->replace_bind_variables($stmt, $this->params);
            else return vsprintf($stmt, $this->params);
        }
        return $stmt;
    }
    
    protected function replace_bind_variables($stmt, $values)
    {
        foreach ($values as $value) $stmt = preg_replace('/\?/i', $this->conn->quote($value), $stmt, 1);
        return $stmt;
    }
    
    protected function replace_named_bind_variables($stmt, $values)
    {
        foreach ($values as $key => $value)
        {
            if (strpos($key, ':') === false) $key = ':'.$key;
            $stmt = preg_replace('/'.$key.'/i', $this->conn->quote($value), $stmt/*, 1*/);
        }
        return $stmt;
    }
}

class SValuesQuerySet extends SQuerySet
{
    public $fields = array();
    
    public function prepare_select()
    {
        if (empty($this->fields)) $fields = '*';
        else $fields = implode(', ', $this->fields);
        return parent::prepare_select($fields);
    }
    
    protected function fetch_row($row)
    {
        if (count($this->fields) == 1) return $row[$this->fields[0]];
        return $row;
    }
}

class SFilledQuerySet extends SQuerySet
{
    public function __construct($meta, $records)
    {
        parent::__construct($meta);
        $this->cache = $records;
    }
}

?>
