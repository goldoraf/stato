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
class Dataset /*extends Statement implements \Iterator, \Countable*/
{
    private $table;
    private $connection;
    private $statement;
    
    public function __construct(Table $table, Connection $conn)
    {
        $this->table = $table;
        $this->connection = $conn;
    }
    
    public function filter()
    {
        $args = func_get_args();
        if (count($args) == 1) {
            if (is_array($args[0])) {
                foreach ($args[0] as $col => $param) {
                    if (is_array($param))
                        $this->appendWhereClause($this->getClauseColumn($col)->in($param));
                    else
                        $this->appendWhereClause($this->getClauseColumn($col)->eq($param));
                }
            }
        } else {
            
        }
        /*foreach ($args as $arg) {
            if (is_array($arg)) {
                if (count($arg) == 1) {
                    list($col, $param) = 
                    $this->appendWhereClause($this->getClauseColumn($col)->eq($param));
                }
                $list = new ExpressionList();
                foreach ($arg as $col => $param) {
                    $list->append($this->getClauseColumn($col)->eq($param));
                }
                $this->appendWhereClause($list);
            }
        }*/
        
        return $this;
    }
    
    private function getClauseColumn($columnName)
    {
        foreach ($this->froms as $table) {
            if (($clauseColumn = $table->getClauseColumn($columnName)) !== false) return $clauseColumn;
        }
        throw new UnknownColumn("{$columnName} not found in this dataset");
    }
}
