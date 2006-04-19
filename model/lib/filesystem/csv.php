<?php

class SCsvIterator implements Iterator
{
    private $fields    = array();
    private $resource  = Null;
    private $data      = false;
    private $line      = 0;
    private $config = array
    (
        'length'    => 4096,
        'separator' => ';',
        'delimiter' => '"',
        'encoding'  => 'ASCII',
        'convert_encoding' => true,
        'has_fields_in_first_line' => true
    );
    
    public function __construct($resource, $config = array())
    {
        $this->resource = $resource;
        $this->config = array_merge($this->config, $config);
        if ($this->config['has_fields_in_first_line'])
        {
            $this->next();
            if ($this->valid()) $this->fields = $this->fetchRow();
        }
    }
    
    public function fields()
    {
        return $this->fields;
    }
    
    public function replaceFields($fields)
    {
        $this->fields = $fields;
    }
    
    public function current()
    {
        if ($this->config['has_fields_in_first_line']) return $this->fetchAssoc();
        else return $this->fetchRow();
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
    
    private function fetchRow()
    {
        $row = array();
        foreach ($this->data as $key => $value) 
            $row[$key] = $this->convertEncoding($value);
        return $row;
    }
    
    private function fetchAssoc()
    {
        $row = array();
        foreach ($this->fields as $key => $value) 
            $row[$value] = $this->convertEncoding($this->data[$key]);
        return $row;
    }
    
    private function convertEncoding($value)
    {
        if (!$this->config['convert_encoding']) return $value;
        else return mb_convert_encoding($value, "UTF-8", $this->config['encoding']);
    }
}

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
