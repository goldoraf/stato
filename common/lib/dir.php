<?php

class SDir extends DirectoryIterator
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
    
    public static function mkdir($path, $mode = 0777)
    {
        return mkdir($path, $mode);
    }
    
    public static function exists($path)
    {
        return is_dir($path);
    }
    
    public static function unlink($path)
    {
        return unlink($path);
    }
    
    public static function mkdirs($dir, $mode = 0777, $recursive = true)
    {
        if (is_null($dir) || $dir === "") return false;
        if (is_dir($dir) || $dir === "/") return true;
        if (self::mkdirs(dirname($dir), $mode, $recursive)) return mkdir($dir, $mode);
        return false;
    }
    
    public static function rmdirs($path)
    {   
        if (!self::exists($path)) return false;
        
        $dir = new RecursiveDirectoryIterator($path);
        foreach($dir as $entry)
        {
            if ($entry->isDir()) self::rmdirs($entry->getPathname());
            else unlink($entry->getPathname());
        }
        unset($dir);
        rmdir($path);
        return true;
    }
    
    public static function copy($source, $dest, $overwrite = false)
    {
        if (!self::exists($dest))
            throw new Exception('Destination directory does not exist.');
            
        $dir = new DirectoryIterator($source);
        foreach ($dir as $file)
        {
            if ($file->isDot() || $file->getFilename() == '.svn') continue;
            if ($file->isFile()) 
            {
                if (!is_file($dest.'/'.$file->getFilename()) || $overwrite)
                {
                    if (!@copy($source.'/'.$file->getFilename(), $dest.'/'.$file->getFilename()))
                        throw new Exception('File '.$file->getFilename().' could not be copied.');
                }
            }
            elseif ($file->isDir())
            {
                if (!self::exists($dest.'/'.$file->getFilename()))
                    self::mkdir($dest.'/'.$file->getFilename());
                self::copy($source.'/'.$file->getFilename(), $dest.'/'.$file->getFilename());
            }
        }
        unset($dir);
    }
    
    public static function entries($dirname)
    {
        if (!self::exists($dirname))
            throw new Exception('Directory does not exist.');
            
        $entries = array();
        $dir = new DirectoryIterator($dirname);
        foreach ($dir as $file) if ($file->isFile()) $entries[] = $file->getFilename();
        unset($dir);
        return $entries;
    }
}

?>
