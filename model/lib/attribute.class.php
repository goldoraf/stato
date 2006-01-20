<?php

class Attribute
{
    public $name    = Null;
    public $type    = Null;
    public $default = Null;
    public $options = array
    (
        'required'    => False,
        'unique'      => False,
        'protected'   => False,
        'validations' => array()
    );
    
    public function __construct($name, $type, $default, $options = array(), $dontTypecastDefault = False)
    {
        $this->name    = $name;
        $this->type    = $type;
        if ($dontTypecastDefault) $this->default = $default;
        else $this->default = $this->typecast($default);
        $this->options = array_merge($this->options, $options);
    }
    
    public function typecast($data)
    {
        if ($data == Null) return Null;
        
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
                return $this->stringToDateTime($data);
                break;
            case 'date':
                return $this->stringToDate($data);
                break;
            case 'boolean':
                return $data === true or strtolower($data) == 'true' or $data == 1;
                break;
            case 'list':
                return explode('|', $data);
                break;
        }
    }
    
    public function stringToDate($data)
    {
        if (is_string($data))
        {
            try { $date = Date::parse($data); }
            catch (Exception $e) { return Null; }
            return $date;
        }
        return $data;
    }
    
    public function stringToDateTime($data)
    {
        if (is_string($data))
        {
            try { $date = DateTime::parse($data); }
            catch (Exception $e) { return Null;  }
            return $date;
        }
        return $data;
    }
}

?>
