<?php

class Stato_UnknownColumnType extends Exception {}

class Stato_Column extends Stato_ClauseColumn
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
    public $foreignKey;
    public $nullable;
    public $default;
    public $length;
    public $index;
    public $unique;
    public $autoIncrement;
    
    public function __construct($name, $type, $options = array())
    {
        $this->name = $name;
        $this->type = $type;
        $this->primaryKey = $this->popOption($options, 'primary_key', false);
        $this->foreignKey = $this->popOption($options, 'foreign_key', false);
        $this->nullable = $this->popOption($options, 'nullable', !$this->primaryKey);
        $this->default = $this->popOption($options, 'default', false);
        $this->length = $this->popOption($options, 'length', null);
        $this->index = $this->popOption($options, 'index', false);
        $this->unique = $this->popOption($options, 'unique', false);
        $this->autoIncrement = $this->popOption($options, 'auto_increment', false);
        
        if (is_string($this->foreignKey)) 
            $this->foreignKey = new Stato_ForeignKey($this->foreignKey);
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
    public $autoload;
    public $primaryKey;
    public $foreignKeys;
    public $constraints;
    
    public function __construct($name, $columns = null)
    {
        $this->name = $name;
        $this->autoload = ($columns === null);
        $this->primaryKey = false;
        $this->foreignKeys = array();
        $this->constraints = array();
        if ($columns !== null)
            foreach ($columns as $column) $this->addColumn($column);
    }
    
    public function addColumn(Stato_Column $column)
    {
        if ($column->primaryKey) $this->primaryKey = $column->name;
        if ($column->foreignKey) {
            $column->foreignKey->setParent($column);
            $this->foreignKeys[] = $column->foreignKey;
            $this->constraints[] = new Stato_ForeignKeyConstraint(array($column->foreignKey), array());
        }
        $column->table = $this;
        $this->columns[$column->name] = $column;
    }
    
    public function getCorrespondingColumn($columnName)
    {
        if (!array_key_exists($columnName, $this->columns)) return false;
        return $this->columns[$columnName];
    }
}

class Stato_ForeignKey
{
    private $column;
    private $parent;
    
    public function __construct($column)
    {
        $this->column = $column;
    }
    
    public function getReferentColumn(Stato_Table $table)
    {
        if (!$this->column instanceof Stato_Column) {
            list(, $column) = explode('.', $this->column);
            $this->column = $table->getCorrespondingColumn($column);
        }
        
        return $this->column;
    }
    
    public function setParent(Stato_Column $column)
    {
        $this->parent = $column;
    }
    
    public function getParent()
    {
        return $this->parent;
    }
}

class Stato_ForeignKeyConstraint
{
    public function __construct(array $columns, array $refColumns, $name = null)
    {
        
    }
}