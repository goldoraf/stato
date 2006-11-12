<?php

class SFolder extends DirectoryIterator
{
    public function __construct($path)
    {
        parent::__construct($path);
    }
    
    public function current()
    {
        return parent::get_file_name();
    }
    
    public function valid()
    {
        if (parent::valid())
        {
            if (!parent::is_file())
            {
                parent::next();
                return $this->valid();
            }
            return True;
        }
        return False;
    }
    
    public function rewind()
    {
        parent::rewind();
    }
}

?>
