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
    
    public static function unlink($path)
    {
        return unlink($path);
    }
    
    public static function copy($source, $dest, $overwrite = false)
    {
        if (!self::exists($dest))
            throw new SException('Destination directory does not exist.');
            
        $dir = new DirectoryIterator($source);
        foreach ($dir as $file)
        {
            if ($file->isDot()) continue;
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
