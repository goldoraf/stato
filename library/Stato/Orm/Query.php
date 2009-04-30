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
    private $table;
    private $pk;
    private $connection;
    private $criterion;
    private $count;
    private $cache;
    
    public function __construct($entity, Connection $connection)
    {
        $this->entity = $entity;
        $this->table = Mapper::getTable($this->entity);
        $this->pk = $this->table->getPrimaryKeyColumn();
        $this->connection = $connection;
        $this->criterion = new ExpressionList(array());
        $this->count = 0;
        $this->cache = null;
    }
    
    public function __clone()
    {
        $this->count = 0;
        $this->cache = null;
        $this->criterion = clone $this->criterion;
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

    public function rewind()
    {
        $this->count = 0;
    }

    public function valid()
    {
        if ($this->cache === null) $this->cache = $this->all();
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
     * Performs a SELECT COUNT() and returns the number of records as an integer.
     * 
     * If the queryset is already cached, if returns the length of the cached result set.          
     */
    public function count()
    {
        if ($this->cache !== null) return count($this->cache);
        
        // TODO
    }
    
    /**
     * Retrieves the result set as an array.
     */
    public function all()
    {
        $stmt = $this->connection->execute($this->getStatement());
        $stmt->setFetchMode(\PDO::FETCH_CLASS, $this->entity);
        return $stmt->fetchAll();
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
     * Returns a new <var>Stato_Query</var> instance with the args ANDed to the existing set.   
     */
    public function filter()
    {
        $criteria = func_get_args();
        $clone = clone $this;
        foreach ($criteria as $c) $clone->appendCriterion($c);
        return $clone;
    }
    
    public function appendCriterion($criterion)
    {
        if (!is_string($criterion) && !$criterion instanceof ClauseElement)
            throw new QueryException('filter() arguments must be instances of ClauseElement or strings');
        
        $this->criterion->append($criterion);
    }
    
    protected function getStatement()
    {
        return $this->table->select()->where($this->criterion);
    }
}