<?php

class SDependencies
{
    public static function requireDependencies($layer, $dependencies, $relativeTo = null)
    {
        foreach ($dependencies as $dependency) self::requireDependency($layer, $dependency, $relativeTo);
    }
    
    public static function requireDependency($layer, $dependency, $relativeTo = null)
    {
        list($subdir, $class) = self::dependencySubDir($dependency, $relativeTo);
        $path = APP_DIR."/$layer/$subdir".SInflection::underscore($class).'.php';
        if (!file_exists($path))
            throw new SException("Missing ".SInflection::singularize($layer)." $dependency");
        require_once($path);
    }
    
    public static function subDirectory($relativeTo)
    {
        if ($relativeTo === null) return '';
        
        $reflection = new ReflectionClass($relativeTo);
        return self::fileSubDirectory($reflection->getFileName());
    }
    
    public static function fileSubDirectory($file)
    {
        $relativeDir = preg_replace('#'.APP_DIR.'/(\w+)/#i', '', 
                        str_replace('\\', '/', $file));
        if (strpos($relativeDir, '/') !== false)
        {
            list($subdir, $file) = explode('/', $relativeDir);
            return $subdir.'/';
        }
        return '';
    }
    
    private static function dependencySubDir($dependency, $relativeTo)
    {
        if (strpos($dependency, '/') === false)
            return array(self::subDirectory($relativeTo), $dependency);
        elseif (strpos($dependency, '/') == 0)
            return array('', substr($dependency, 1));
        else
        {
            list($subdir, $dependency) = explode('/', $dependency);
            return array($subdir.'/', $dependency);
        }
    }
}

?>
