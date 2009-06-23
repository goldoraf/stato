<?php

namespace Stato\Orm;

require_once 'Expression.php';

class UnknownColumnType extends Exception {}

class Column extends ClauseColumn
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
            $this->foreignKey = new ForeignKey($this->foreignKey);
    }
    
    private function popOption($options, $optionName, $optionDefault)
    {
        if (!array_key_exists($optionName, $options)) return $optionDefault;
        $optionValue = $options[$optionName];
        unset($options[$optionName]);
        return $optionValue;
    }
}

class Table extends TableClause
{
    protected $primaryKey;
    protected $foreignKeys;
    protected $constraints;
    
    public function __construct($name, array $columns)
    {
        $this->name = $name;
        $this->primaryKey = false;
        $this->foreignKeys = array();
        $this->constraints = array();
        foreach ($columns as $column) $this->addColumn($column);
    }
    
    public function addColumn(Column $column)
    {
        if ($column->primaryKey) $this->primaryKey = $column->name;
        if ($column->foreignKey) {
            $column->foreignKey->setParent($column);
            $this->foreignKeys[] = $column->foreignKey;
            $this->constraints[] = new ForeignKeyConstraint(array($column->foreignKey), array());
        }
        $column->table = $this;
        $this->columns[$column->name] = $column;
    }
    
    public function getCorrespondingColumn($columnName)
    {
        if (!array_key_exists($columnName, $this->columns)) return false;
        return $this->columns[$columnName];
    }
    
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }
    
    public function getPrimaryKeyColumn()
    {
        return $this->columns[$this->primaryKey];
    }
    
    public function getForeignKeys()
    {
        return $this->foreignKeys;
    }
}

class ForeignKey
{
    private $column;
    private $parent;
    
    public function __construct($column)
    {
        $this->column = $column;
    }
    
    public function getReferentColumn(Table $table)
    {
        if (!$this->column instanceof Column) {
            list(, $column) = explode('.', $this->column);
            $this->column = $table->getCorrespondingColumn($column);
        }
        
        return $this->column;
    }
    
    public function setParent(Column $column)
    {
        $this->parent = $column;
    }
    
    public function getParent()
    {
        return $this->parent;
    }
}

class ForeignKeyConstraint
{
    public function __construct(array $columns, array $refColumns, $name = null)
    {
        
    }
}