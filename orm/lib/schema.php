<?php

class Stato_UnknownColumnType extends Exception {}

class Stato_Column
{
    const INTEGER   = 'integer';
    const STRING    = 'string';
    const BOOLEAN   = 'boolean';
    const DATE      = 'date';
    const DATETIME  = 'datetime';
    const TIMESTAMP = 'timestamp';
    const FLOAT     = 'float';
    const TEXT      = 'text';
    
    public $name;
    public $type;
    public $primaryKey;
    public $nullable;
    public $default;
    public $length;
    public $index;
    public $unique;
    public $autoIncrement;
    
    /**
     * Constructor
     *
     * 
     */
    public function __construct($name, $type, $options = array())
    {
        $this->name = $name;
        $this->type = $type;
        $this->primaryKey = $this->popOption($options, 'primary_key', false);
        $this->nullable = $this->popOption($options, 'nullable', !$this->primaryKey);
        $this->default = $this->popOption($options, 'default', false);
        $this->length = $this->popOption($options, 'length', null);
        $this->index = $this->popOption($options, 'index', false);
        $this->unique = $this->popOption($options, 'unique', false);
        $this->autoIncrement = $this->popOption($options, 'auto_increment', false);
    }
    
    private function popOption($options, $optionName, $optionDefault)
    {
        if (!array_key_exists($optionName, $options)) return $optionDefault;
        $optionValue = $options[$optionName];
        unset($options[$optionName]);
        return $optionValue;
    }
}

class Stato_Table extends Stato_TableClause
{
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