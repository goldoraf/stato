<?php

class SRecordNotFound extends Exception {}
class SAssertionError extends Exception {}

/**
 * ORM query class
 * 
 * <var>SQuerySet</var> objects represents lazy database lookups for a set of objects.
 * 
 * @package Stato
 * @subpackage orm
 */
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
    
    protected $count = 0;
    protected $cache = null;
    protected $meta  = null;
    protected $conn  = null;
    
    protected $custom_sql  = null;
    protected $pk_lookup   = array();
    protected $schema_abbr = array();
    
    public function __construct($meta)
    {
        $this->meta = $meta;
        $this->conn = SActiveRecord::connection();
    }
    
    public function __clone()
    {
        $this->count = 0;
        $this->cache = null;
    }
    
    public function __toString()
    {
        return $this->prepare_select();
    }
    
    /**
     * Iterator method
     */
    public function current()
    {
        return $this->cache[$this->count];
    }
    
    /**
     * Iterator method
     */
    public function key()
    {
        return $this->count;
    }

    /**
     * Iterator method
     */
    public function next()
    {
        $this->count++;
    }

    /**
     * Iterator method
     */
    public function rewind()
    {
        $this->count = 0;
    }

    /**
     * Iterator method
     */
    public function valid()
    {
        if ($this->cache === null) $this->fetch_all();
        return isset($this->cache[$this->count]);
    }
    
    /**
     * Retrieves the first item of the result set.
     */
    public function first()
    {
        $this->rewind();
        if ($this->valid()) return $this->current();
        else return null;
    }
    
    /**
     * Retrieves the result set as an array.
     */
    public function to_array()
    {
        if ($this->cache === null) $this->fetch_all();
        return $this->cache;
    }
    
    /**
     * Alias of <var>to_array()</var>.
     */
    public function serializable_form()
    {
        return $this->to_array();   
    }
    
    /**
     * Performs a SELECT and returns a single object matching the given arguments.
     */
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
        if (($count = count($set)) < 1) throw new SRecordNotFound();
        elseif ($count == 1) return $set[0];
        else throw new SAssertionError();
    }
    
    /**
     * Returns an array mapping each of the given IDs to the object with that ID.
     */
    public function in_bulk(array $ids = null)
    {
        if (is_null($ids)) {
            $clone = clone $this;
        } else {
            if (count($ids) == 0) return array();
            $clone = call_user_func_array(array($this, 'filter'), array($this->meta->identity_field.' IN ('.join(',', $ids).')'));
            $clone->limit = null;
            $clone->offset = null;
        }
        
        $set = array();
        foreach ($clone as $o) $set[$o->id] = $o;
        return $set;
    }
    
    /**
     * Performs a SELECT COUNT() and returns the number of records as an integer.
     * 
     * If the queryset is already cached, if returns the length of the cached result set.          
     */
    public function count()
    {
        if ($this->cache !== null) return count($this->cache);
        
        $clone = clone $this;
        $clone->order_by = array();
        
        $offset = $clone->offset;
        $limit  = $clone->limit;
        
        $clone->offset = null;
        $clone->limit  = null;
        
        if (!empty($this->includes)) {
            $rs = $this->conn->select('SELECT COUNT(*) FROM ('.$clone->prepare_select().') AS tbl');
        } else {
            $rs = $this->conn->select($clone->prepare_select('COUNT(*)'));
        }
        $row = $this->conn->fetch($rs, false);
        $count = $row[0];
        
        if ($offset !== null)
            $count = max(0, $count - $offset);
        if ($limit !== null)
            $count = min($limit, $count);
            
        return $count;
    }
    
    /**
     * Deletes the records in the current queryset.       
     */
    public function delete()
    {
        $clone = clone $this;
        $clone->includes = array();
        $clone->joins    = array();
        $clone->order_by = array();
        $this->conn->execute("DELETE FROM {$this->meta->table_name} ".$clone->sql_clause());
        return;
    }
    
     /**
     * Updates the records in the current queryset.       
     */
    public function update($values)
    {
        $clone = clone $this;
        $clone->includes = array();
        $clone->joins    = array();
        $clone->order_by = array();
        $this->conn->execute("UPDATE {$this->meta->table_name} ".$clone->sql_set($values).' '.$clone->sql_clause());
        return;
    }
    
    /**
     * Returns a new <var>SQuerySet</var> instance with the args ANDed to the existing set.   
     */
    public function filter()
    {
        return $this->filter_or_exclude(func_get_args());
    }
    
    /**
     * Returns a new <var>SQuerySet</var> instance with the NOT(args) ANDed to the existing set.   
     */
    public function exclude()
    {
        return $this->filter_or_exclude(func_get_args(), true);
    }
    
    public function filter_by(array $args)
    {
        return $this->filter_or_exclude_by($args);
    }
    
    public function exclude_by(array $args)
    {
        return $this->filter_or_exclude_by($args, true);
    }
    
    /**
     * Allows the user to provide a custom SQL condition.   
     */
    public function by_sql($sql)
    {
        $this->custom_sql = $sql;
        return $this;
    }
    
    /**
     * Returns a new <var>SQuerySet</var> instance with a limited result set.   
     */
    public function limit($limit, $offset = 0)
    {
        $clone = clone $this;
        $clone->limit = $limit;
        $clone->offset = $offset;
        return $clone;
    }
    
    /**
     * Returns a new <var>SQuerySet</var> instance with the ordering changed.   
     */
    public function order_by()
    {
        $args = func_get_args();
        $clone = clone $this;
        $clone->order_by = $args;
        return $clone;
    }
    
    /**
     * Allows the user to provide a custom SQL join.   
     */
    public function join($sql)
    {
        $clone = clone $this;
        $clone->joins[] = $sql;
        return $clone;
    }
    
    /**
     * Returns a new <var>SQuerySet</var> instance which will use a SELECT DISTINCT query.   
     */
    public function distinct()
    {
        $clone = clone $this;
        $clone->distinct = true;
        return $clone;
    }
    
    /**
     * Returns a new <var>SQuerySet</var> instance which will use joins to retrieve associated objects.   
     */
    public function includes()
    {
        $args = func_get_args();
        $clone = clone $this;
        $clone->includes = array_merge($this->includes, $args);
        return $clone;
    }
    
    /**
     * Returns a new <var>SValuesQuerySet</var> instance which will return values intead of objects.   
     */
    public function values()
    {
        $args = func_get_args();
        if (count($args) == 1 && is_array($args[0])) $args = $args[0];
        $v = new SValuesQuerySet($this->meta);
        $v->filters  = $this->filters;
        $v->joins    = $this->joins;
        $v->excludes = $this->excludes;
        $v->params   = $this->params;
        $v->order_by = $this->order_by;
        $v->limit    = $this->limit;
        $v->offset   = $this->offset;
        $v->distinct = $this->distinct;
        if (!empty($args)) $v->fields = $args;
        return $v;
    }
    
    /**
     * Returns the generated SQL condition.
     */
    public function sql_clause()
    {
        $components = array();
        $components[] = $this->sql_joins();
        $components[] = $this->sql_conditions();
        $components[] = $this->sql_order_by();
        $components[] = $this->sql_limit();
        foreach ($components as $k => $v) if ($v === null) unset($components[$k]);
        return $this->conn->sanitize_sql(implode(' ', $components), $this->params);
    }
    
    /**
     * Returns a generated SQL SET
     */
    public function sql_set($values)
    {
        $set = array();
        foreach ($values as $k => $v)
            $set[] = "`$k` = ".$this->conn->quote($v);
        
        return 'SET '.join(',', $set);
    }
    
    /**
     * Returns the generated SQL query.
     */
    public function prepare_select($fields = null)
    {
        if ($this->custom_sql !== null) return $this->custom_sql;
        
        if ($fields === null)
        {
            if (empty($this->includes) && empty($this->joins)) $fields = '*';
            elseif (empty($this->includes) && !empty($this->joins))
            {
                $columns = array_keys($this->conn->columns($this->meta->table_name));
                foreach ($columns as $k => $column) 
                    $columns[$k] = $this->meta->table_name.'.'.$column;
                $fields = implode(', ', $columns);
            }
            else $fields = implode(', ', $this->column_aliases());
        }
        $select = ($this->distinct) ? 'SELECT DISTINCT' : 'SELECT';
        return implode(' ', array($select, $fields, "FROM {$this->meta->table_name}", $this->sql_clause()));
    }
    
    /**
     * Instantiates records and deals with the single table inheritance model.
     */
    public function instanciate_record($row, $meta = null, $new_record = false)
    {
        if ($meta === null) $meta = $this->meta;
        
        if ($row !== null 
            && in_array($meta->inheritance_field, array_keys($meta->attributes))
            && isset($row[$meta->inheritance_field])) 
            $class = SInflection::camelize($row[$meta->inheritance_field]);
        else
            $class = $meta->class;
        
        if ($new_record) $record = new $class($row);
        else
        {
            $record = new $class();
            $record->hydrate($row);
        }
        
        if (count($meta->decorators) != 0)
        {
            // the @ is a dirty fix for a stupid notice added to "fix" http://bugs.php.net/bug.php?id=38146
            foreach (@$meta->decorators as $decorator => $config)
            {
                $decorator_class = 'S'.SInflection::camelize($decorator).'Decorator';
                $record = new $decorator_class($record, $config);
            }
        }
        return $record;
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
                $assoc_name = $matches[1];
                if (!isset($this->meta->attributes[$assoc_name]))
                    throw new Exception('Association '.$assoc_name.' does not exist.');
                
                $assoc_meta = $this->meta->attributes[$assoc_name]->meta;
                $field = $matches[2];
                $cond  = $matches[3];
                
                if (!in_array($assoc_name, $this->includes))
                    $clone->add_join($this->association_join($assoc_meta));
                    
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
    
    protected function filter_or_exclude_by($args, $exclude = false)
    {
        $criterions = array();
        $binds = array();
        foreach ($args as $k => $v) {
            if (is_array($v)) {
                $in = array_fill(0, count($v), '?');
                foreach ($v as $bind) $binds[] = $bind;
                $criterions[] = "{$k} IN (".implode(',', $in).")";
            } else {
                $criterions[] = "{$k} = ?";
                $binds[] = $v;
            }
        }
        $criterions[] = $binds;
        return $this->filter_or_exclude($criterions, $exclude);
    }
    
    protected function add_join($sql)
    {
        if (!array_search($sql, $this->joins)) $this->joins[] = $sql;
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
            if ($o{0} == '-') $order = substr($o, 1).' DESC';
            else $order = "$o ASC";
            $orders[] = $order;
        }
        return 'ORDER BY '.implode(', ', $orders);
    }
    
    protected function type_condition()
    {
        return $this->meta->inheritance_field.' = \''.SInflection::underscore($this->meta->class).'\'';
    }
    
    protected function fetch_all()
    {   
        if (!empty($this->includes))
        {
            $this->fetch_all_with_assocs();
            return;
        }
        
        $class = $this->meta->class;
        $rows  = $this->conn->select_all($this->prepare_select());
        $this->cache = array();
        foreach ($rows as $row)
            $this->cache[] = $this->instanciate_record($row);
    }
    
    protected function fetch_all_with_assocs()
    {
        $this->meta->load_associations();
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
        
        $resource = $this->conn->select($this->prepare_select());
        
        while($row = $this->conn->fetch($resource))
        {
            $id = $row[$pk];
            if (!isset($records[$id]))
                $records_in_order[] = $records[$id] = $this->instanciate_record($this->extract_record($this->meta->table_name, $row));
            
            foreach ($this->includes as $k)
            {
                $assoc_meta = $this->meta->attributes[$k]->meta;
                
                if (isset($row[$this->pk_lookup[$assoc_meta->table_name]]))
                {
                    $record = $this->extract_record($assoc_meta->table_name, $row);
                    if ($record)
                    {
                        $assoc = $this->instanciate_record($record, $assoc_meta);
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
                    $records[$id]->$k->populate(new SFilledQuerySet($this->meta->attributes[$k]->meta, array_values($records_many_assocs[$id][$k])));
                else
                    $records[$id]->$k->populate(new SFilledQuerySet($this->meta->attributes[$k]->meta, array()));
            }
        }
                
        $this->conn->free_result($resource);
        $this->cache = $records_in_order;
    }
    
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
}

class SValuesQuerySet extends SQuerySet
{
    public $fields = array();
    
    public function prepare_select($fields = null)
    {
        if (empty($this->fields)) $fields = '*';
        else $fields = implode(', ', $this->fields);
        return parent::prepare_select($fields);
    }
    
    public function instanciate_record($row, $meta = null, $new_record = false)
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

class SRawQuerySet implements IteratorAggregate
{
    protected $sql;
    protected $meta;
    protected $params;
    protected $conn;
    protected $iter;
    
    public function __construct($sql, $meta, array $params = array())
    {
        $this->sql = $sql;
        $this->meta = $meta;
        $this->params = $params;
        $this->conn = SActiveRecord::connection();
    }
    
    public function __toString()
    {
        return $this->conn->sanitize_sql($this->sql, $this->params);
    }
    
    public function getIterator()
    {
        if (!isset($this->iter)) $this->iter = new ArrayIterator($this->fetch_all());
        return $this->iter;
    }
    
    protected function fetch_all()
    {
        $rows  = $this->conn->select_all($this->__toString());
        $records = array();
        foreach ($rows as $row)
            $records[] = $this->instanciate_record($row);
        
        return $records;
    }
    
    /**
     * Ripped from SQuerySet ; TODO : refactoring in STableMap class ?
     */
    protected function instanciate_record($row)
    {
       if (in_array($this->meta->inheritance_field, array_keys($this->meta->attributes)) && isset($row[$this->meta->inheritance_field])) 
            $class = SInflection::camelize($row[$this->meta->inheritance_field]);
        else
            $class = $this->meta->class;
        
        $record = new $class();
        $record->hydrate($row);
        
        if (count($this->meta->decorators) != 0)
        {
            // the @ is a dirty fix for a stupid notice added to "fix" http://bugs.php.net/bug.php?id=38146
            foreach (@$this->meta->decorators as $decorator => $config)
            {
                $decorator_class = 'S'.SInflection::camelize($decorator).'Decorator';
                $record = new $decorator_class($record, $config);
            }
        }
        return $record;
    }
}

?>
