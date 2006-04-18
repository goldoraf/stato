<?php

class SColumn
{
    public $name    = Null;
    public $type    = Null;
    public $limit   = Null;
    public $default = Null;
    public $null    = True;
    
    public function __construct($name, $type, $limit = Null, $default = Null, $null = True)
    {
        $this->name    = $name;
        $this->type    = $type;
        $this->limit   = $limit;
        $this->default = $default;
        $this->null    = $null;
    }
    
    public function toSql()
    {
        $db = SActiveRecord::connection();
        $sql = $db->quoteColumnName($this->name).' '.$db->typeToSql($this->type, $this->limit);
        $sql = $db->addColumnOptions($sql, array('null' => $this->null, 'default' => $this->default));
        return $sql;
    }
}

class STable
{
    private $columns = array();
    private $hasPk = false;
    
    public function __construct()
    {
    
    }
    
    public function addPrimaryKey($name, $type = 'primary_key', $options = array())
    {
        $this->addColumn($name, $type, $options);
        $this->hasPk = true;
    }
    
    public function addColumn($name, $type, $options = array())
    {
        if (!is_object($name))
        {
            $column = new SColumn($name, $type);
            if (isset($options['limit']))   $column->limit = $options['limit'];
            if (isset($options['default'])) $column->default = $options['default'];
            if (isset($options['null']))    $column->null = $options['null'];
        }
        else $column = $name;
        
        $this->columns[] = $column;
    }
    
    public function toSql()
    {
        if (!$this->hasPk) $this->addPrimaryKey('id');
        $cols = array();
        foreach($this->columns as $column) $cols[] = $column->toSql();
        return implode(', ', $cols);
    }
}

?>
