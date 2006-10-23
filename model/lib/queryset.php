<?php

class SActiveRecordDoesNotExist extends SException {}

class SQuerySet implements Iterator, Countable
{
    public $filters  = array();
    public $excludes = array();
    public $includes = array();
    public $params   = array();
    public $orderBy  = array();
    public $joins    = array();
    public $offset   = null;
    public $limit    = null;
    public $distinct = false;
    
    protected $resource = null;
    protected $count    = 0;
    protected $cache    = null;
    protected $meta     = null;
    protected $conn     = null;
    
    protected $pkLookup   = array();
    protected $schemaAbbr = array();
    
    public function __construct($meta)
    {
        $this->meta = $meta;
        $this->conn = SActiveRecord::connection();
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
            $this->resource = $this->conn->select($this->prepareSelect());
            $this->cache = array();
            if (!empty($this->includes))
            {
                $this->fetchAllWithAssocs();
                return !empty($this->cache);
            }
        }
        return $this->fetch();
    }
    
    public function dump()
    {
        if (!is_array($this->cache))
        {
            $this->resource = $this->conn->select($this->prepareSelect());
            if (!empty($this->includes)) $this->fetchAllWithAssocs();
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
            $pk = $this->meta->identityField;
            if (!empty($this->includes)) $pk = $this->meta->tableName.'.'.$pk;
            
            if (is_array($args[0])) 
                $args[] = $pk.' IN ('.join(',', $args[0]).')';
            elseif (is_numeric($args[0]))
                $args[] = $pk.' = \''.$args[0].'\'';
            else 
                $args[] = $args[0];
            unset($args[0]);
        }
        $clone = call_user_func_array(array($this, 'filter'), $args);
        $set = $clone->dump();
        if (($count = count($set)) < 1) throw new SActiveRecordDoesNotExist();
        elseif ($count == 1) return $set[0];
        else
        {
            $newSet = array();
            foreach ($set as $o) $newSet[$o->id] = $o;
            return $newSet;
        }
    }
    
    public function count()
    {
        if ($this->resource !== null) return $this->conn->rowCount($this->resource);
        elseif ($this->resource === null && is_array($this->cache)) return count($this->cache);
        else
        {
            $clone = $this->selfClone();
            $clone->order_by = array();
            $clone->includes = array();
            $clone->offset   = null;
            $clone->limit    = null;
            
            $rs = $this->conn->select($clone->prepareSelect('COUNT(*)'));
            $row = $this->conn->fetch($rs, false);
            return $row[0];
        }
    }
    
    public function delete()
    {
        $clone = $this->selfClone();
        $clone->includes = array();
        $clone->joins    = array();
        $clone->orderBy   = array();
        $this->conn->execute("DELETE FROM {$this->meta->tableName} ".$clone->sqlClause());
        return;
    }
    
    public function filter()
    {
        return $this->filterOrExclude(func_get_args());
    }
    
    public function exclude()
    {
        return $this->filterOrExclude(func_get_args(), true);
    }
    
    public function limit($limit, $offset = 0)
    {
        $clone = $this->selfClone();
        $clone->limit = $limit;
        $clone->offset = $offset;
        return $clone;
    }
    
    public function orderBy()
    {
        $args = func_get_args();
        $clone = $this->selfClone();
        $clone->orderBy = array_merge($this->orderBy, $args);
        return $clone;
    }
    
    public function join($sql)
    {
        $clone = $this->selfClone();
        $clone->joins[] = $sql;
        return $clone;
    }
    
    public function distinct()
    {
        $clone = $this->selfClone();
        $clone->distinct = true;
        return $clone;
    }
    
    public function includes()
    {
        $args = func_get_args();
        $clone = $this->selfClone();
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
        $v->orderBy  = $this->orderBy;
        $v->limit    = $this->limit;
        $v->offset   = $this->offset;
        $v->distinct = $this->distinct;
        if (!empty($args)) $v->fields = $args;
        return $v;
    }
    
    public function sqlClause()
    {
        $components = array();
        $components[] = $this->sqlJoins();
        $components[] = $this->sqlConditions();
        $components[] = $this->sqlOrderBy();
        $components[] = $this->sqlLimit();
        foreach ($components as $k => $v) if ($v === null) unset($components[$k]);
        return $this->sanitizeSql(implode(' ', $components));
    }
    
    public function prepareSelect($fields = null)
    {
        if ($fields === null)
        {
            if (empty($this->includes)) $fields = '*';
            else $fields = implode(', ', $this->columnAliases());
        }
        $select = ($this->distinct) ? 'SELECT DISTINCT' : 'SELECT';
        return implode(' ', array($select, $fields, "FROM {$this->meta->tableName}", $this->sqlClause()));
    }
    
    protected function fetch()
    {
        $class = $this->meta->class;
        $row = $this->conn->fetch($this->resource);
        if (!$row)
        {
            $this->conn->freeResult($this->resource);
            $this->resource = null;
            return false;
        }
        $this->cache[] = $this->fetchRow($row);
        return true;
    }
    
    protected function fetchRow($row)
    {
        $class = $this->meta->class;
        return new $class($row);
    }
    
    protected function filterOrExclude($args, $exclude = false)
    {
        $numargs = count($args);
        $clone = $this->selfClone();
        
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
                    throw new SException('Association '.$matches[1].' does not exist.');
                $assocMeta = $this->meta->attributes[$matches[1]]->meta;
                if ($assocMeta->type == 'SManyToMany')
                {
                    /*$clone->joins[] = "LEFT OUTER JOIN {$assocMeta->joinTable} 
                                       ON {$assocMeta->tableName}.{$assocMeta->identityField} 
                                       = {$assocMeta->joinTable}.{$assocMeta->assocForeignKey}";
                    $clone->filters[] = "{$assocMeta->joinTable}.{$assocMeta->foreignKey} = '{$this->owner->id}'"*/
                }
                else
                {
                    $table = $assocMeta->tableName;
                    $field = $matches[2];
                    $cond  = $matches[3];
                    $clone->joins[] = "LEFT OUTER JOIN $table ON $table.{$field}{$cond}";
                }
                unset($args[$k]);
            }
        }
        
        if (count($args) == 0) return $clone;
        elseif (count($args) == 1) $condition = array_pop($args);
        else $condition = '('.implode(' AND ', $args).')';
        
        if ($exclude) $condition = 'NOT '.$condition;
        $clone->filters[] = $condition;
        
        return $clone;
    }
    
    protected function sqlJoins()
    {
        if (empty($this->joins) && empty($this->includes)) return null;
        return implode(' ', array_merge($this->includeJoins(), $this->joins));
    }
    
    protected function sqlConditions()
    {
        if ($this->meta->descendsFrom() != 'SActiveRecord') $this->filters[] = $this->typeCondition();
        if (empty($this->filters)) return null;
        return 'WHERE '.implode(' AND ', $this->filters);
    }
    
    protected function sqlLimit()
    {
        if ($this->limit === null) return null;
        return $this->conn->limit($this->limit, $this->offset);
    }
    
    protected function sqlOrderBy()
    {
        if (empty($this->orderBy)) return null;
        $orders = array();
        foreach ($this->orderBy as $o)
        {
            if ($o{0} == '-') $orders[] = substr($o, 1).' DESC';
            else $orders[] = "$o ASC";
        }
        return 'ORDER BY '.implode(', ', $orders);
    }
    
    protected function typeCondition()
    {
        return $this->meta->inheritanceField.' = \''.strtolower($this->meta->class).'\'';
    }
    
    protected function fetchAllWithAssocs()
    {
        $pk = $this->meta->tableName.'_'.$this->meta->identityField;
        $class = $this->meta->class;
        $records = array();
        $recordsInOrder = array();
        $recordsManyAssocs = array();
        $manyAssocs = array();
        
        foreach ($this->includes as $k)
        {
            $assocMeta = $this->meta->attributes[$k]->meta;
            if ($assocMeta->type == 'SHasMany' || $assocMeta->type == 'SManyToMany') $manyAssocs[] = $k;
        }
        
        while($row = $this->conn->fetch($this->resource))
        {
            $id = $row[$pk];
            if (!isset($records[$id]))
                $recordsInOrder[] = $records[$id] = new $class($this->extractRecord($this->meta->tableName, $row));
            
            foreach ($this->includes as $k)
            {
                $assocMeta = $this->meta->attributes[$k]->meta;
                
                if (isset($row[$this->pkLookup[$assocMeta->tableName]]))
                {
                    $record = $this->extractRecord($assocMeta->tableName, $row);
                    if ($record)
                    {
                        $assocClass = $assocMeta->class;
                        $assoc = new $assocClass($record);
                        if (in_array($k, $manyAssocs))
                        {
                            if (!isset($recordsManyAssocs[$id][$k][$assoc->id])) 
                                $recordsManyAssocs[$id][$k][$assoc->id] = $assoc;
                        } 
                        else $records[$id]->$k->setTarget($assoc);
                    }
                }
            }
        }
        
        foreach ($records as $id => $record)
        {
            foreach ($manyAssocs as $k)
            {
                if (isset($recordsManyAssocs[$id][$k]))
                    $records[$id]->$k->setQuerySet(new SFilledQuerySet($this->meta->attributes[$k]->meta, array_values($recordsManyAssocs[$id][$k])));
                else
                    $records[$id]->$k->setQuerySet(new SFilledQuerySet($this->meta->attributes[$k]->meta, array()));
            }
        }
                
        $this->conn->freeResult($this->resource);
        $this->resource = null;
        $this->cache = $recordsInOrder;
    }
    
    // n'instancie pas les records dont ttes les values sont NULL !!!
    protected function extractRecord($tableName, $row)
    {
        $record = array();
        $valid = false;
        foreach($row as $key => $value)
        {
            list($prefix, $column) = $this->schemaAbbr[$key];
            if ($prefix == $tableName)
            {
                $record[$column] = $value;
                if ($value != null) $valid = true;
            }
        }
        if ($valid == true) return $record;
        return false;
    }
    
    protected function columnAliases()
    {
        $aliases = array();
        $tables = array($this->meta->tableName);
        foreach ($this->includes as $r) $tables[$r] = $this->meta->attributes[$r]->meta->tableName;
        foreach ($tables as $r => $t)
        {
            $columns = array_keys($this->conn->columns($t));
            
            foreach($columns as $column)
            {
                $abbr = $t.'_'.$column;
                $this->schemaAbbr[$abbr] = array($t, $column);
                $aliases[] = join(array($t, $column), '.').' AS '.$abbr;
                if (is_string($r) && $column == $this->meta->attributes[$r]->meta->identityField) $this->pkLookup[$t] = $abbr;
            }
        }
        return $aliases;
    }
    
    protected function includeJoins()
    {
        $joins = array();
        foreach ($this->includes as $r)
        {
            $assocMeta = $this->meta->attributes[$r]->meta;
            switch($assocMeta->type)
            {
                case 'SBelongsTo':
                    $joins[] = "LEFT OUTER JOIN {$assocMeta->tableName} ON "
                    ."{$assocMeta->tableName}.{$assocMeta->identityField} = "
                    ."{$this->meta->tableName}.{$assocMeta->foreignKey}";
                    break;
                    
                case 'SManyToMany':
                    $joins[] = "LEFT OUTER JOIN {$assocMeta->joinTable} ON "
                    ."{$assocMeta->joinTable}.{$assocMeta->foreignKey} = "
                    ."{$this->meta->tableName}.{$this->meta->identityField}";
                    
                    $joins[] = "LEFT OUTER JOIN {$assocMeta->tableName} ON "
                    ."{$assocMeta->joinTable}.{$assocMeta->assocForeignKey} = "
                    ."{$assocMeta->tableName}.{$assocMeta->identityField}";
                    break;
                    
                default:
                    $joins[] = "LEFT OUTER JOIN {$assocMeta->tableName} ON "
                    ."{$assocMeta->tableName}.{$assocMeta->foreignKey} = "
                    ."{$this->meta->tableName}.{$this->meta->identityField}";
            }
        }
        return $joins;
    }
    
    protected function sanitizeSql($stmt)
    {
        if (!empty($this->params))
        {
            if (strpos($stmt, ':')) return $this->replaceNamedBindVariables($stmt, $this->params);
            elseif (strpos($stmt, '?')) return $this->replaceBindVariables($stmt, $this->params);
            else return vsprintf($stmt, $this->params);
        }
        return $stmt;
    }
    
    protected function replaceBindVariables($stmt, $values)
    {
        foreach ($values as $value) $stmt = preg_replace('/\?/i', $this->conn->quote($value), $stmt, 1);
        return $stmt;
    }
    
    protected function replaceNamedBindVariables($stmt, $values)
    {
        foreach ($values as $key => $value)
        {
            if (strpos($key, ':') === false) $key = ':'.$key;
            $stmt = preg_replace('/'.$key.'/i', $this->conn->quote($value), $stmt, 1);
        }
        return $stmt;
    }
    
    protected function selfClone()
    {
        $class = __CLASS__;
        $clone = new $class($this->meta);
        $clone->filters  = $this->filters;
        $clone->excludes = $this->excludes;
        $clone->includes = $this->includes;
        $clone->params   = $this->params;
        $clone->orderBy  = $this->orderBy;
        $clone->joins    = $this->joins;
        $clone->offset   = $this->offset;
        $clone->limit    = $this->limit;
        $clone->distinct = $this->distinct;
        return $clone;
    }
}

class SValuesQuerySet extends SQuerySet
{
    public $fields = array();
    
    public function prepareSelect()
    {
        if (empty($this->fields)) $fields = '*';
        else $fields = implode(', ', $this->fields);
        return parent::prepareSelect($fields);
    }
    
    protected function fetchRow($row)
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
