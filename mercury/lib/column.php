<?php

class SColumn
{
    const PK        = 'primary_key';
    const INTEGER   = 'integer';
    const STRING    = 'string';
    const BOOLEAN   = 'boolean';
    const DATE      = 'date';
    const DATETIME  = 'datetime';
    const TIMESTAMP = 'timestamp';
    const FLOAT     = 'float';
    const TEXT      = 'text';
    
    public $name    = null;
    public $type    = null;
    public $limit   = null;
    public $default = null;
    public $null    = true;
    
    public function __construct($name, $type, $default = null, $limit = null, $null = true)
    {
        $this->name    = $name;
        $this->type    = $type;
        $this->limit   = $limit;
        $this->default = $default;
        $this->null    = $null;
    }
    
    public function typecast($owner, $data)
    {
        if ($data === null || $data === '') return null;
        
        switch($this->type)
        {
            case self::INTEGER:
                return (integer) $data;
            case self::FLOAT:
                return (float) $data;
            case self::DATETIME:
                return $this->string_to_date_time($data);
            case self::DATE:
                return $this->string_to_date($data);
            case self::BOOLEAN:
                return $data === true or strtolower($data) == 'true' or $data == 1;
            default:
                return $data;
        }
    }
    
    public function default_value($owner)
    {
        return $this->typecast($owner, $this->default);
    }
    
    public function string_to_date($data)
    {
        if ($data instanceof SDate) return $data;
        try { $date = SDate::parse($data); }
        catch (Exception $e) { return null; }
        return $date;
    }
    
    public function string_to_date_time($data)
    {
        if ($data instanceof SDateTime) return $data;
        try { $date = SDateTime::parse($data); }
        catch (Exception $e) { return null;  }
        return $date;
    }
    
    public function to_sql()
    {
        $db = SActiveRecord::connection();
        $sql = $db->quote_column_name($this->name).' '.$db->type_to_sql($this->type, array('limit' => $this->limit));
        $sql = $db->add_column_options($sql, $this->type, array('null' => $this->null, 'default' => $this->default));
        if ($this->type === self::PK) $sql.= ' PRIMARY KEY';
        return $sql;
    }
}

?>
