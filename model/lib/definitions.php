<?php

class SColumn
{
    public $name    = Null;
    public $type    = Null;
    public $limit   = Null;
    public $default = Null;
    public $null    = true;
    public $primary = false;
    
    public function __construct($name, $type, $limit = Null, $default = Null, $null = true)
    {
        $this->name    = $name;
        $this->type    = $type;
        $this->limit   = $limit;
        $this->default = $default;
        $this->null    = $null;
    }
    
    public function to_sql()
    {
        $db = SActiveRecord::connection();
        $sql = $db->quote_column_name($this->name).' '.$db->type_to_sql($this->type, $this->limit);
        $sql = $db->add_column_options($sql, $this->type, array('null' => $this->null, 'default' => $this->default));
        if ($this->primary) $sql.= ' PRIMARY KEY';
        return $sql;
    }
}

class STable
{
    private $columns = array();
    private $has_pk   = false;
    
    public function __construct()
    {
    
    }
    
    public function add_primary_key($name, $type = 'primary_key', $options = array())
    {
        $this->add_column($name, $type, $options);
        $this->has_pk = true;
        $this->columns[$name]->primary = true;
    }
    
    public function add_column($name, $type, $options = array())
    {
        if (!is_object($name))
        {
            $column = new SColumn($name, $type);
            if (isset($options['limit']))   $column->limit = $options['limit'];
            if (isset($options['default'])) $column->default = $options['default'];
            if (isset($options['null']))    $column->null = $options['null'];
        }
        else $column = $name;
        
        $this->columns[$name] = $column;
    }
    
    public function to_sql()
    {
        //if (!$this->has_pk) $this->add_primary_key('id');
        $cols = array();
        foreach($this->columns as $column) $cols[] = $column->to_sql();
        return implode(', ', $cols);
    }
}

?>
