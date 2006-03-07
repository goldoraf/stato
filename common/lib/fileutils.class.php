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
}

?>
