<?php

class STable
{
    private $columns  = array();
    private $has_pk   = false;
    
    public function add_primary_key($name, $type = SColumn::PK, $options = array())
    {
        $this->add_column($name, $type, $options);
        $this->has_pk = true;
    }
    
    public function add_column($name, $type, $options = array())
    {
        if (!is_object($name))
        {
            $column = new SColumn($name, $type);
            if (isset($options['limit']))   $column->limit   = $options['limit'];
            if (isset($options['default'])) $column->default = $options['default'];
            if (isset($options['null']))    $column->null    = $options['null'];
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
