<?php

class STempfile
{
    private $path;
    
    public function __construct($tmpdir = null, $basename = null)
    {
        if ($basename === null) $basename = self::tmpname();
        if ($tmpdir === null) $tmpdir = SDir::tmpdir();
        $this->path = $tmpdir.'/'.$basename;
    }
    
    public function path()
    {
        return $this->path;
    }
    
    public static function tmpname($length = 10, $allowed_chars = 'abcdefghjkmnpqrstuvwxyz0123456789')
    {
        srand((double)microtime() * 1000000);
        $name = '';
        for ($i=0; $i<$length; $i++)
            $name.= $allowed_chars{(rand() % 33)};
            
        return $name;
    }
    
    public function __destruct()
    {
        @unlink($this->path);
    }
}

?>