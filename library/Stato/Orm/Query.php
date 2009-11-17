<?php

namespace Stato\Orm;

require_once 'Expression.php';

class QueryException extends Exception {}

class RecordNotFound extends Exception {}

/**
 * ORM query class
 * 
 * <var>Query</var> objects represents lazy database lookups for a set of objects.
 * 
 * @package Stato
 * @subpackage Orm
 */
class Query implements \Iterator, \Countable
{
    private $entity;
    private $mapper;
    private $table;
    private $pk;
    private $connection;
    private $count;
    private $cache;
    private $result;
    
    
    public function __construct(Mapper $mapper, Connection $connection)
    {
        $this->mapper = $mapper;
        $this->entity = $this->mapper->entity;
        $this->table = $this->mapper->table;
        $this->pk = $this->table->getPrimaryKeyColumn();
        $this->connection = $connection;
        $this->count = 0;
        $this->cache = null;
        $this->result = null;
    }
    
    public function __clone()
    {
        $this->count = 0;
        $this->cache = null;
        $this->result = null;
        if (isset($this->criterion)) 
            $this->criterion = clone $this->criterion;
        if (isset($this->orderBy)) 
            $this->orderBy = clone $this->orderBy;
    }
    
    public function __toString()
    {
        return $this->getStatement()->__toString();
    }
    
    public function rewind()
    {
        if ($this->cache === null) {
            $this->result = $this->execute();
            $this->cache = array();
        }
        $this->count = 0;
    }

    public function valid()
    {
        if ($this->result !== null) {
            if (($entity = $this->result->fetch()) !== false) {
                $this->cache[] = $entity;
            } else {
                $this->result->close();
            }
        }
        return isset($this->cache[$this->count]);
    }
    
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
    
    /**
     * Performs a SELECT COUNT() and returns the number of records as an integer.
     * 
     * If the queryset is already cached, if returns the length of the cached result set.          
     */
    public function count()
    {
        // TODO
    }
    
    /**
     * Retrieves the result set as an array.
     */
    public function all()
    {
        $set = array();
        foreach ($this as $entity) $set[] = $entity;
        return $set;
    }
    
    /**
     * Retrieves the first item of the result set.
     */
    public function first()
    {
        // TODO
    }
    
    /**
     * Retrieves the last item of the result set.
     */
    public function last()
    {
        // TODO
    }
    
    /**
     * Performs a SELECT and returns a single object matching the given ID.
     */
    public function get($id)
    {
        $this->appendCriterion($this->pk->eq($id));
        $set = $this->all();
        if (count($set) < 1) throw new RecordNotFound();
        return $set[0];
    }
    
    /**
     * Returns an array mapping each of the given IDs to the object with that ID.
     */
    public function inBulk($ids)
    {
         if (!is_array($ids))
            throw new QueryException('inBulk() must be provided with a list of IDs');
        
        if (count($ids) == 0) return array();
        
        $this->appendCriterion($this->pk->in($ids));
        $set = array();
        foreach ($this as $o) $set[$o->{$this->pk->name}] = $o;
        return $set;
    }
    
    /**
     * Returns a new <var>Query</var> instance with the args ANDed to the existing set.   
     */
    public function filter()
    {
        $criteria = func_get_args();
        $clone = clone $this;
        foreach ($criteria as $c) $clone->appendCriterion($c);
        return $clone;
    }
    
    public function filterBy(array $values)
    {
        $clone = clone $this;
        foreach ($values as $columnName => $value) {
            $column = $this->table->{$columnName};
            if (is_array($value))
                $clone->appendCriterion($column->in($value));
            else
                $clone->appendCriterion($column->eq($value));
        }
        return $clone;
    }
    
    /**
     * Returns a new <var>Query</var> instance with a limited result set.   
     */
    public function limit($limit, $offset = 0)
    {
        $clone = clone $this;
        $clone->limit = $limit;
        $clone->offset = $offset;
        return $clone;
    }
    
    /**
     * Returns a new <var>Query</var> instance with the ordering changed.   
     */
    public function orderBy()
    {
        $args = func_get_args();
        $clone = clone $this;
        foreach ($args as $arg) {
            /*if (!is_string($arg) && !$arg instanceof ClauseElement)
                throw new QueryException('orderBy() arguments must be instances of ClauseElement or strings');*/
            if (is_string($arg)) {
                $desc = false;
                if ($arg{0} == '-') {
                    $arg = substr($arg, 1);
                    $desc = true;
                }
                $column = $this->table->{$arg};
                if ($desc) $arg = $column->desc();
                else $arg = $column->asc();
            }
            $clone->appendOrderBy($arg);
        }
        return $clone;
    }
    
    public function appendCriterion($criterion)
    {
        if (!isset($this->criterion)) $this->criterion = new ExpressionList();
        /*if (!is_string($criterion) && !$criterion instanceof ClauseElement)
            throw new QueryException('filter() arguments must be instances of ClauseElement or strings');*/
        
        if (is_callable($criterion)) $criterion = $criterion($this->table);
        
        $this->criterion->append($criterion);
    }
    
    public function appendOrderBy($clause)
    {
        if (!isset($this->orderBy)) $this->orderBy = new ClauseList();
        $this->orderBy->append($clause);
    }
    
    private function execute()
    {
        $res = $this->connection->execute($this->getStatement());
        $this->mapper->setFetchMode($res);
        return $res;
    }
    
    private function getStatement()
    {
        $stmt = $this->table->select();
        
        if (isset($this->criterion)) 
            $stmt = call_user_func_array(array($stmt, 'where'), $this->criterion->expressions);
        
        if (isset($this->limit)) $stmt = $stmt->limit($this->limit);
        if (isset($this->offset)) $stmt = $stmt->offset($this->offset);
        if (isset($this->orderBy)) $stmt = $stmt->orderBy($this->orderBy);
        
        return $stmt;
    }
}