<?php

namespace Stato\Orm;

class DatasetException extends Exception {}
class RecordNotFound extends Exception {}

/**
 * ORM dataset class
 * 
 * <var>Dataset</var> objects represents lazy database lookups for a set of objects.
 * 
 * @package Stato
 * @subpackage Orm
 */
class Dataset implements \IteratorAggregate, \Countable
{
    private $table;
    private $connection;
    private $mapper;
    private $session;
    private $statement;
    private $iterator;
    
    public function __construct(Table $table, Connection $conn, Mapper $mapper = null, Session $session = null, Statement $stmt = null)
    {
        if (is_null($stmt)) $stmt = new Select(array($table));
        
        $this->table = $table;
        $this->connection = $conn;
        $this->mapper = $mapper;
        $this->session = $session;
        $this->statement = $stmt;
        $this->iterator = null;
    }
    
    public function __toString()
    {
        $compiler = new Compiler();
        return $compiler->compile($this->statement)->string;
    }
    
    public function __clone()
    {
        $this->statement = clone $this->statement;
        $this->iterator = null;
    }
    
    /**
     * Returns an iterator over the results from executing the dataset's resulting SQL.
     */
    public function getIterator()
    {
        if (is_null($this->iterator)) {
            $res = $this->connection->execute($this->statement);
            if (!is_null($this->mapper))
                $this->iterator = new DatasetIterator($res, $this->mapper->getHydrator($this->connection->getDialect()), $this->session);
            else
                $this->iterator = new DatasetIterator($res);
        }
        return $this->iterator;
    }
    
    public function toArray()
    {
        $set = array();
        if (is_null($this->iterator)) $this->getIterator();
        foreach ($this->iterator as $row) $set[] = $row;
        return $set;
    }
    
    /**
     * Performs a SELECT COUNT() and returns the number of records as an integer.
     * 
     * If the dataset is already cached, if returns the length of the cached result set.          
     */
    public function count()
    {
        // TODO
    }
    
    /**
     * Retrieves the first item of the result set.
     */
    public function first()
    {
        if (is_null($this->iterator)) $this->getIterator();
        $this->iterator->rewind();
        if (!$this->iterator->valid())
            throw new RecordNotFound();
        return $this->iterator->current();
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
        $clone = $this->filter($this->table->getPrimaryKeyColumn()->eq($id));
        return $clone->first();
    }
    
    /**
     * Returns an array mapping each of the given IDs to the object with that ID.
     */
    public function inBulk($ids)
    {
        // TODO
    }
    
    /**
     * Returns a copy of the dataset with the given conditions imposed upon it. 
     * 
     * If the query already has a HAVING clause, then the conditions are 
     * imposed in the HAVING clause. If not, then they are imposed in the WHERE clause.
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
     * Returns a copy of the dataset with a list of equality/inclusion expressions imposed upon it. 
     * 
     * These expressions are constructed with the array passed as argument.
     * Example :
     * $db->filterBy(array('id' => 1));
     *   => SQL : SELECT * FROM items WHERE id = 1;
     * $db->filterBy(array('id' => array(1,2)));
     *   => SQL : SELECT * FROM items WHERE id IN (1,2);
     */
    public function filterBy(array $args)
    {
        return $this->filterOrExclude($this->prepareFilterArgs($args));
    }
    
    /**
     * Performs the inverse of Dataset::filterBy().
     */
    public function excludeBy(array $args)
    {
        return $this->filterOrExclude($this->prepareFilterArgs($args), true);
    }
    
    /**
     * Returns a new dataset with a limited result set.   
     */
    public function limit($limit, $offset = 0)
    {
        $stmt = clone $this->statement;
        $stmt->limit($limit);
        if ($offset > 0) $stmt->offset($offset);
        return $this->newInstance($stmt);
    }
    
    /**
     * Returns a new dataset with the ordering changed.   
     */
    public function orderBy()
    {
        $args = func_get_args();
        $stmt = clone $this->statement;
        foreach ($args as $k => $arg) {
            if (is_string($arg)) {
                $desc = false;
                if ($arg{0} == '-') {
                    $arg = substr($arg, 1);
                    $desc = true;
                }
                $column = $this->table->{$arg};
                if ($desc) $arg = $column->desc();
                else $arg = $column->asc();
                $args[$k] = $arg;
            } elseif (is_callable($arg)) {
                $args[$k] = $arg($this->table);
            }
        }
        call_user_func_array(array($stmt, 'orderBy'), $args);
        return $this->newInstance($stmt);
    }
    
    private function filterOrExclude($args, $exclude = false)
    {
        $stmt = clone $this->statement;
        foreach ($args as $k => $arg) {
            if (is_callable($arg)) {
                $args[$k] = $arg($this->table);
            }
        }
        if (!$exclude) {
            call_user_func_array(array($stmt, 'where'), $args);
        } elseif (count($args) == 1) {
            $stmt->where(not_($args[0]));
        } else {
            $stmt->where(not_(new ExpressionList($args)));
        }
        return $this->newInstance($stmt);
    }
    
    private function prepareFilterArgs(array $args)
    {
        $newArgs = array();
        foreach ($args as $col => $param) {
            if (is_array($param))
                $newArgs[] = $this->table->{$col}->in($param);
            else
                $newArgs[] = $this->table->{$col}->eq($param);
        }
        return $newArgs;
    }
    
    private function newInstance(Statement $stmt = null)
    {
        return new Dataset($this->table, $this->connection, $this->mapper, $this->session, $stmt);
    }
}

class DatasetIterator implements \Iterator
{
    private $pos;
    private $cache;
    private $result;
    private $hydrator;
    private $session;
    
    public function __construct(ResultProxy $result, ObjectHydrator $hydrator = null, Session $session = null)
    {
        $this->pos = 0;
        $this->cache = array();
        $this->result = $result;
        $this->hydrator = $hydrator;
        $this->session = $session;
    }
    
    public function rewind()
    {
        $this->pos = 0;
    }

    public function valid()
    {
        if (isset($this->cache[$this->pos])) return true;
        if (is_null($this->result)) return false;
        return $this->fetch();
    }
    
    public function current()
    {
        return $this->cache[$this->pos];
    }
    
    public function key()
    {
        return $this->pos;
    }
    
    public function next()
    {
        $this->pos++;
    }
    
    private function fetch()
    {
        if (($row = $this->result->fetch()) === false) {
            $this->result->close();
            $this->result = null;
            return false;
        }
        if (!is_null($this->hydrator)) $row = $this->hydrator->newInstance($row, $this->session);
        $this->cache[] = $row;
        return true;
    }
}
