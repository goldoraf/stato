<?php

class Column
{
    public $name    = Null;
    public $type    = Null;
    public $limit   = Null;
    public $default = Null;
    public $null    = True;
    
    public function __construct($name, $type, $limit = Null, $default = Null, $null = False)
    {
        $this->name    = $name;
        $this->type    = $type;
        $this->limit   = $limit;
        $this->default = $default;
        $this->null    = $null;
    }
    
    public function toSql()
    {
        $db = Database::getInstance();
        $sql = $db->quoteColumnName($this->name).' '.Schema::typeToSql($this->type, $this->limit);
        $sql = Schema::addColumnOptions($sql, array('null' => $this->null, 'default' => $this->default));
        return $sql;
    }
}

class Table
{
    private $columns = array();
    
    public function __construct()
    {
    
    }
    
    public function addPrimaryKey($name)
    {
        $this->addColumn($name, $this->native('primary_key'));
    }
    
    public function addColumn($name, $type = Null, $options = array())
    {
        if (!is_object($name))
        {
            $column = new Column($name, $type);
            if (isset($options['limit']))   $column->limit = $options['limit'];
            if (isset($options['default'])) $column->default = $options['default'];
            if (isset($options['null']))    $column->null = $options['null'];
        }
        else $column = $name;
        
        $this->columns[] = $column;
    }
    
    public function toSql()
    {
        $cols = array();
        foreach($this->columns as $column) $cols[] = $column->toSql();
        return implode(', ', $cols);
    }
    
    private function native($type)
    {
        $db = Database::getInstance();
        return $db->nativeDbTypes[$type];
    }
}

?>
