<?php

class STable
{
    private $name;
    private $columns;
    
    public function __construct($name = null, $columns = array())
    {
        $this->name = $name;
        $this->columns = $columns;
    }
    
    public function add_primary_key($name, $type = SColumn::PK, $options = array())
    {
        $options = array_merge(array('pk' => true, 'null' => false), $options);
        $this->add_column($name, $type, $options);
    }
    
    public function add_column($name, $type, $options = array())
    {
        if (!is_object($name))
        {
            $column = new SColumn($name, $type);
            if (isset($options['limit']))   $column->limit   = $options['limit'];
            if (isset($options['default'])) $column->default = $options['default'];
            if (isset($options['null']))    $column->null    = $options['null'];
            if (isset($options['pk']))      $column->pk      = $options['pk'];
        }
        else $column = $name;
        
        $this->columns[$name] = $column;
    }
    
    public function name()
    {
        return $this->name;
    }
    
    public function to_sql()
    {
        $cols = array();
        foreach($this->columns as $column) $cols[] = $column->to_sql();
        return implode(', ', $cols);
    }
    
    public function create($options = null)
    {
        SActiveRecord::connection()->create_table($this->name, $this, $options);
    }
}

?>
