<?php

abstract class Stato_Statement
{
    public function __toString()
    {
        $compiler = new Stato_DefaultCompiler();
        return $compiler->compile($this);
    }
}

class Stato_Insert extends Stato_Statement
{
    public $table;
    public $values;
    
    public function __construct(Stato_Table $table, $values = null)
    {
        $this->table = $table;
        $this->values = $values;
    }
    
    public function values($values)
    {
        $this->values = $values;
        return $this;
    }
}

class Stato_Select extends Stato_Statement
{
    private $columns;
    
    private $froms;
    
    public function __construct($columns = null, $whereClause = null)
    {
        $this->froms = array();
        $this->columns = array();
        if ($columns !== null) {
            foreach ($columns as $c) {
                if ($c instanceof Stato_Table) 
                    $this->columns = array_merge($this->columns, $c->getColumnClauses());
                else $this->columns[] = $c;
            }
        }
        foreach ($this->columns as $c) 
            if (!in_array($c->table, $this->froms)) $this->froms[] = $c->table;
    }
    
    public function getColumns()
    {
        return $this->columns;
    }
    
    public function getFroms()
    {
        return $this->froms;
    }
}

class Stato_ColumnClause
{
    public $name;
    
    public $table;
    
    public function __construct($name, $table = null)
    {
        $this->name = $name;
        $this->table = $table;
    }
}

class Stato_ColumnCriteria extends Stato_ColumnClause
{
    
}