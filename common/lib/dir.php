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
        $dir = new RecursiveDirectoryIterator($path);
        foreach (new RecursiveIteratorIterator($dir) as $file) unlink($file);
        foreach ($dir as $subDir) if(!@rmdir($subDir)) self::rmdirs($subDir);
        rmdir($path);
    }
    
    public static function copy($source, $dest, $overwrite = false)
    {
        if (!self::exists($dest))
            throw new SException('Destination directory does not exist.');
            
        $dir = new DirectoryIterator($source);
        foreach ($dir as $file)
        {
            if ($file->isDot() || $file->getFilename() == '.svn') continue;
            if ($file->isFile()) 
            {
                if (!is_file($dest.'/'.$file->getFilename()) || $overwrite)
                {
                    if (!@copy($source.'/'.$file->getFilename(), $dest.'/'.$file->getFilename()))
                        throw new SException('File '.$file->getFilename().' could not be copied.');
                }
            }
            elseif ($file->isDir())
            {
                if (!self::exists($dest.'/'.$file->getFilename()))
                    self::mkdir($dest.'/'.$file->getFilename());
                self::copy($source.'/'.$file->getFilename(), $dest.'/'.$file->getFilename());
            }
        }
    }
}

?>
