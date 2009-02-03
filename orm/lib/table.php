<?php

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
    
    public function addColumn(Stato_Column $column)
    {
        if ($column->primaryKey) $this->primaryKey = $column->name;
        $this->columns[$column->name] = $column;
    }
}