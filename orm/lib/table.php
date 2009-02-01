<?php

class Stato_Table
{
    public $name;
    public $columns;
    
    public function __construct($name, $columns = array())
    {
        $this->name = $name;
        $this->columns = $columns;
    }
}