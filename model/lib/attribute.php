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
    
    public function __construct($name, $type, $default = null, $options = array(), $dontTypecastDefault = False)
    {
        $this->name    = $name;
        $this->type    = $type;
        if ($dontTypecastDefault) $this->default = $default;
        else $this->default = $this->typecast($default);
        $this->options = array_merge($this->options, $options);
    }
    
    public function typecast($data)
    {
        if ($data === null) return null;
        
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
            try { $date = SDate::parse($data); }
            catch (Exception $e) { return null; }
            return $date;
        }
        return $data;
    }
    
    public function stringToDateTime($data)
    {
        if (is_string($data))
        {
            try { $date = SDateTime::parse($data); }
            catch (Exception $e) { return null;  }
            return $date;
        }
        return $data;
    }
}

?>
