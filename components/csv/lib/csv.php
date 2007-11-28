<?php

class SCsv
{
    public static function open($path, $mode = 'r', $config = array())
    {
        if ($mode == 'r') return new SCsvIterator(fopen($path, 'r'), $config);
        return new SCsvWriter(fopen($path, 'w'), $config);
    }
}

class SCsvWriter
{
    private $resource = null;
    private $config   = array
    (
        'separator' => ';',
        'delimiter' => '"',
        'encode_to'  => 'UTF-8',
        'detectable_encodings' => "UTF-8, ISO-8859-1, ISO-8859-15",
        'convert_encoding' => false
    );
    
    public function __construct($resource, $config = array())
    {
        if (!is_resource($resource)) 
            throw new Exception('Resource provided is not valid');
            
        $this->resource = $resource;
        $this->config = array_merge($this->config, $config);
    }
    
    public function add_row($row)
    {
        foreach ($row as $key => $value) 
            $row[$key] = $this->convert_encoding($value);
        
        fputcsv($this->resource, $row, $this->config['separator'], $this->config['delimiter']);
    }
    
    public function close()
    {
        fclose($this->resource);
    }
    
    private function convert_encoding($value)
    {
        if (!$this->config['convert_encoding']) return $value;
        
        $from_encoding = mb_detect_encoding($value, $this->config['detectable_encodings'], true);
        if ($from_encoding == $this->config['encode_to']) return $value;
        return mb_convert_encoding($value, $this->config['encode_to'], $from_encoding);
    }
}

class SCsvIterator implements Iterator
{
    private $fields    = array();
    private $resource  = null;
    private $data      = false;
    private $line      = 0;
    private $config = array
    (
        'length'    => 4096,
        'separator' => ';',
        'delimiter' => '"',
        'encode_to'  => 'UTF-8',
        'detectable_encodings' => "UTF-8, ISO-8859-1, ISO-8859-15",
        'convert_encoding' => false,
        'has_fields_in_first_line' => true
    );
    
    public function __construct($resource, $config = array())
    {
        if (!is_resource($resource)) 
            throw new Exception('Resource provided is not valid');
            
        $this->resource = $resource;
        $this->config = array_merge($this->config, $config);
        if ($this->config['has_fields_in_first_line'])
        {
            $this->next();
            if ($this->valid()) $this->fields = $this->fetch_row();
        }
    }
    
    public function fields()
    {
        return $this->fields;
    }
    
    public function assign_fields($fields)
    {
        foreach ($fields as $k => $v) $this->fields[$k] = $v;
    }
    
    public function current()
    {
        if ($this->config['has_fields_in_first_line']) return $this->fetch_assoc();
        else return $this->fetch_row();
    }
    
    public function key()
    {
        return $this->line;
    }
    
    public function next()
    {
        $this->data = fgetcsv($this->resource, $this->config['length'], 
                              $this->config['separator'], $this->config['delimiter']);
        $this->line++;
    }
    
    public function valid()
    {
        if (!$this->data) return false;
        return true;
    }
    
    public function rewind()
    {
        $this->line = 0;
        rewind($this->resource);
        if ($this->config['has_fields_in_first_line']) $this->next();
        $this->next();
    }
    
    private function fetch_row()
    {
        $row = array();
        foreach ($this->data as $key => $value) 
            $row[$key] = $this->convert_encoding($value);
        return $row;
    }
    
    private function fetch_assoc()
    {
        if (count($this->fields) != count($this->data))
            throw new Exception("Wrong field count at line {$this->line}");
        
        $row = array();
        foreach ($this->fields as $key => $value) 
            $row[$value] = $this->convert_encoding($this->data[$key]);
        return $row;
    }
    
    private function convert_encoding($value)
    {
        if (!$this->config['convert_encoding']) return $value;
        
        $from_encoding = mb_detect_encoding($value, $this->config['detectable_encodings'], true);
        if ($from_encoding == $this->config['encode_to']) return $value;
        return mb_convert_encoding($value, $this->config['encode_to'], $from_encoding);
    }
}

// Usage : 
// $str = "php,stato,framework";
// $fp = fopen("csvstr://str", "r+");
class SCsvStream
{
    private $position;
    private $varname;
    
    public function stream_open($path, $mode, $options, &$opened_path)
    { 
        $url = parse_url($path);
        $this->varname = $url['host'];
        $this->position = 0;
        return true;
    }
    
    public function stream_read($count)
    { 
        $ret = substr($GLOBALS[$this->varname], $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }
    
    public function stream_eof()
    { 
        return $this->position >= strlen($GLOBALS[$this->varname]); 
    } 
    
    public function stream_tell()
    { 
        return $this->position;
    }
    
    public function stream_seek($offset, $whence)
    {
        switch($whence) 
        {
            case SEEK_SET:
                if ($offset < strlen($GLOBALS[$this->varname]) && $offset >= 0) {
                    $this->position = $offset;
                    return true;
                } else return false;
                break;
            
            case SEEK_CUR:
                if ($offset >= 0) {
                    $this->position += $offset;
                    return true;
                } else return false;
                break;
            
            case SEEK_END:
                if (strlen($GLOBALS[$this->varname]) + $offset >= 0) {
                    $this->position = strlen($GLOBALS[$this->varname]) + $offset;
                    return true;
                } else return false;
                break;
            
            default:
                return false;
        }
    }
}

stream_wrapper_register("csvstr", "SCsvStream");

?>
