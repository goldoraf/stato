<?php

class SAttribute
{
    public $name    = null;
    public $type    = null;
    public $default = null;
    public $options = array
    (
        'required'    => False,
        'unique'      => False,
        'protected'   => False,
        'validations' => array()
    );
    
    public function __construct($name, $type, $default = null, $options = array()/*, $dont_typecast_default = False*/)
    {
        $this->name    = $name;
        $this->type    = $type;
        /*if ($dont_typecast_default) */$this->default = $default;
        //else $this->default = $this->typecast($default);
        $this->options = array_merge($this->options, $options);
    }
    
    public function typecast($owner, $data)
    {
        if ($data === null) return null;
        if (in_array($this->type, array('boolean', 'integer', 'float')) && $data === '') return null;
        
        switch($this->type)
        {
            case 'string':
                return $data;
                break;
            case 'text':
                return $data;
                break;
            case 'integer':
                return (integer) $data;
                break;
            case 'float':
                return (float) $data;
                break;
            case 'datetime':
                return $this->string_to_date_time($data);
                break;
            case 'date':
                return $this->string_to_date($data);
                break;
            case 'boolean':
                return $data === true or strtolower($data) == 'true' or $data == 1;
                break;
        }
    }
    
    public function default_value($owner)
    {
        //return $this->default;
        return $this->typecast($owner, $this->default);
    }
    
    public function string_to_date($data)
    {
        if (get_class($data) == 'SDate') return $data;
        try { $date = SDate::parse($data); }
        catch (Exception $e) { return null; }
        return $date;
    }
    
    public function string_to_date_time($data)
    {
        if (get_class($data) == 'SDateTime') return $data;
        try { $date = SDateTime::parse($data); }
        catch (Exception $e) { return null;  }
        return $date;
    }
}

?>
