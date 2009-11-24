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
class Dataset /*implements \Iterator, \Countable*/
{
    private $table;
    private $connection;
    private $statement;
    
    public function __construct(Table $table, Connection $conn, Statement $stmt = null)
    {
        if (is_null($stmt)) $stmt = new Select(array($table));
        
        $this->table = $table;
        $this->connection = $conn;
        $this->statement = $stmt;
    }
    
    public function __toString()
    {
        $compiler = new Compiler();
        return $compiler->compile($this->statement)->string;
    }
    
    public function __clone()
    {
        $this->statement = clone $this->statement;
    }
    
    public function filter()
    {
        return $this->filterOrExclude(func_get_args());
    }
    
    public function exclude()
    {
        return $this->filterOrExclude(func_get_args(), true);
    }
    
    public function filterBy(array $args)
    {
        return $this->filterOrExclude($this->prepareFilterArgs($args));
    }
    
    public function excludeBy(array $args)
    {
        return $this->filterOrExclude($this->prepareFilterArgs($args), true);
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
        return new Dataset($this->table, $this->connection, $stmt);
    }
}
