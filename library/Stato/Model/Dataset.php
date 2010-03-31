<?php

namespace Stato\Model;

use \ArrayObject, \IteratorAggregate, \Countable, \Exception;

class RecordNotFound extends Exception {}

class Dataset implements IteratorAggregate, Countable
{
    private $metaclass;
    
    private $repository;
    
    private $query;
    
    private $iterator;
    
    public function __construct(Metaclass $metaclass, Repository $repository)
    {
        $this->metaclass = $metaclass;
        $this->repository = $repository;
        $this->query = new Query($metaclass);
        $this->iterator = null;
    }
    
    public function __clone()
    {
        $this->query = clone $this->query;
        $this->iterator = null;
    }
    
    public function getQuery()
    {
        return $this->query;
    }
    
    /**
     * Returns an iterator over the results from executing the dataset's query
     */
    public function getIterator()
    {
        if (is_null($this->iterator)) {
            $this->iterator = new ArrayObject($this->all());
        }
        return $this->iterator;
    }
    
    /**
     * Returns the number of results as an integer
     * 
     * If the dataset is already cached (i.e. has been iterated), if returns the length of the cached result set.          
     */
    public function count()
    {
        // TODO : With most of NoSQL engines it will be necessary to fetch the result set before counting
        // maybe a if ($this->getAdapter()->supportsCount()) ?
    }
    
    /**
     * Returns all objects matching the query conditions
     */
    public function all()
    {
        return $this->repository->read($this->query);
    }
    
    /**
     * Returns a single object matching the given key values
     */
    public function get()
    {
        $key = func_get_args();
        $records = $this->filterOrExclude($this->getKeyConditions($key))->all();
        if (count($records) == 0) {
            throw new RecordNotFound;
        }
        return $records[0];
    }
    
    /**
     * Returns an array mapping each of the given IDs to the object with that ID
     */
    public function inBulk($ids)
    {
        // TODO
    }
    
    /**
     * Retrieves the first item of the result set
     */
    public function first()
    {
        $records = $this->all();
        return $records[0];
    }
    
    /**
     * Retrieves the last item of the result set
     */
    public function last()
    {
        // TODO
    }
    
    /**
     * Returns a copy of the dataset with the given conditions imposed upon it
     */
    public function filter()
    {
        return $this->filterOrExclude(func_get_args());
    }
    
    /**
     * Performs the inverse of Dataset::filter().
     */
    public function exclude()
    {
        return $this->filterOrExclude(func_get_args(), true);
    }
    
    /**
     * Returns a new dataset with a limited result set
     */
    public function limit($limit, $offset = 0)
    {
        $clone = clone $this;
        $clone->getQuery()->update(array('limit' => $limit, 'offset' => $offset));
        return $clone;
    }
    
    /**
     * Returns a new dataset with the ordering changed 
     */
    public function orderBy()
    {
        $args = func_get_args();
        $sorts = array();
        foreach ($args as $k => $arg) {
            if (is_callable($arg)) {
                $sort = $arg($this->metaclass);
                if (!$sort instanceof Query\Sort) {
                    throw new Exception('A closure used as a sort in a query must return a Query\Sort instance');
                }
            } elseif (is_string($arg)) {
                $desc = false;
                if ($arg{0} == '-') {
                    $arg = substr($arg, 1);
                    $desc = true;
                }
                $property = $this->metaclass->{$arg};
                $sort = ($desc) ? $property->desc() : $property->asc();
            } elseif ($arg instanceof Query\Sort) {
                $sort = $arg;
            } else {
                // TODO : better exception message
                throw new Exception('Unknown query sort type');
            }
            $sorts[] = $sort;
        }
        
        $clone = clone $this;
        $clone->getQuery()->update(array('sorts' => $sorts));
        return $clone;
    }
    
    private function filterOrExclude($args, $exclude = false)
    {
        if (count($args) == 1 && is_array($args[0])) {
            $args = $args[0];
        }
        
        $conditions = array();
        foreach ($args as $k => $arg) {
            if (is_callable($arg)) {
                $condition = $arg($this->metaclass);
                if (!$condition instanceof Query\Condition) {
                    throw new Exception('A closure used as a condition in a query must return a Query\Condition instance');
                }
            } elseif (is_string($k)) {
                if (is_array($arg)) {
                    $condition = $this->metaclass->{$k}->in($arg);
                } else {
                    $condition = $this->metaclass->{$k}->eq($arg);
                }
            } elseif ($arg instanceof Query\Condition) {
                $condition = $arg;
            } else {
                // TODO : better exception message
                throw new Exception('Unknown query condition type');
            }
            
            if ($exclude) {
                $condition->negate();
            }
            $conditions[] = $condition;
        }
        
        $clone = clone $this;
        $clone->getQuery()->update(array('conditions' => $conditions));
        return $clone;
    }
    
    private function getKeyConditions($key)
    {
        $keyProperties = $this->metaclass->getKey();
        $conditions = array();
        foreach ($keyProperties as $k => $p) $conditions[] = $p->eq($key[$k]);
        return $conditions;
    }
}