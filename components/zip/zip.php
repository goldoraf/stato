<?php

if (!extension_loaded('zip'))
    throw new Exception('ZIP extension is required for SZipIterator component');

class SZipIterator implements Iterator
{
    private $filepath  = null;
    private $resource  = null;
    private $entry     = null;
    private $entry_name = null;
    
    public function __construct($filepath)
    {
        $this->filepath = $filepath;
    }
    
    public function __destruct()
    {
        zip_close($this->resource);
    }
    
    public function next()
    {
        $this->entry = zip_read($this->resource);
        if ($this->valid()) $this->entry_name = zip_entry_name($this->entry);
    }
    
    public function valid()
    {
        if (!$this->entry) return false;
        return true;
    }
    
    public function current()
    {
        if (!zip_entry_open($this->resource, $this->entry))
            throw new Exception("Zip file entry can not be read");
        
        $buffer = zip_entry_read($this->entry, zip_entry_filesize($this->entry));
        zip_entry_close($this->entry);
        return $buffer;
    }
    
    public function key()
    {
        return $this->entry_name;
    }
    
    public function rewind()
    {
        if ($this->resource !== null) zip_close($this->resource);
        $this->entry = null;
        $this->open();
        $this->next();
    }
    
    private function open()
    {
        $this->resource = zip_open($this->filepath);
        if (!$this->resource) 
            throw new Exception("Zip file {$this->filepath} does not exist");
    }
}

?>
