<?php

class SDir
{
    public static function mkdir($path, $mode = 0777)
    {
        return mkdir($path, $mode);
    }
    
    public static function exists($path)
    {
        return is_dir($path);
    }
}

?>
