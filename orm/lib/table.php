<?php

class Stato_UnknownColumn extends Exception {}

class Stato_Table
{
    public $name;
    public $columns = array();
    public $autoload = false;
    public $primaryKey = false;
    
    public function __construct($name, $columns = null)
    {
        $this->name = $name;
        if ($columns === null) $this->autoload = true;
        else foreach ($columns as $column) $this->addColumn($column);
    }
    
    public function __get($columnName)
    {
        if (!array_key_exists($columnName, $this->columns))
            throw new Stato_UnknownColumn("{$columnName} in {$this->table} table");
        
        return new Stato_ColumnCriteria($columnName, $this->name);
    }
    
    public function addColumn(Stato_Column $column)
    {
        if ($column->primaryKey) $this->primaryKey = $column->name;
        $this->columns[$column->name] = $column;
    }
    
    public function getColumnClauses($columns = null)
    {
        $clauses = array();
        if ($columns === null) $columns = $this->columns;
        foreach ($columns as $column) {
            if ($column instanceof Stato_Column)
                $clauses[] = new Stato_ColumnClause($column->name, $this->name);
            elseif ($column instanceof Stato_ColumnClause || $column instanceof Stato_ColumnCriteria)
                $clauses[] = $column;
            else
                $clauses[] = new Stato_ColumnClause($column, $this->name);
        }
        return $clauses;
    }
    
    public function insert($values = null)
    {
        return new Stato_Insert($this, $values);
    }
    
    public function select($columns = null, $whereClause = null)
    {
        return new Stato_Select($this->getColumnClauses($columns), $whereClause);
    }
}