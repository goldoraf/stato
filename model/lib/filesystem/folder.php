<?php

class SFolder extends DirectoryIterator
{
    public function __construct($path)
    {
        parent::__construct($path);
    }
    
    public function current()
    {
        return parent::getFileName();
    }
    
    public function valid()
    {
        if (parent::valid())
        {
            if (!parent::isFile())
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
