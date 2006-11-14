<?php

class SFileUtils
{
    public static function mkdirs($dir, $mode = 0777, $recursive = true)
    {
        if (is_null($dir) || $dir === "") return false;
        if (is_dir($dir) || $dir === "/") return true;
        if (self::mkdirs(dirname($dir), $mode, $recursive)) return mkdir($dir, $mode);
        return false;
    }
    
    public static function rmdirs($path)
    {   
        $dir = new RecursiveDirectoryIterator($path);
        foreach (new RecursiveIteratorIterator($dir) as $file) unlink($file);
        foreach ($dir as $subDir) if(!@rmdir($subDir)) self::rmdirs($subDir);
        rmdir($path);
    }
}

?>
