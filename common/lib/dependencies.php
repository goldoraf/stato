<?php

class SDependencies
{
    public static function require_dependencies($layer, $dependencies, $relative_to = null)
    {
        foreach ($dependencies as $dependency) self::require_dependency($layer, $dependency, $relative_to);
    }
    
    public static function require_dependency($layer, $dependency, $relative_to = null)
    {
        list($subdir, $class) = self::dependency_sub_dir($dependency, $relative_to);
        if (class_exists($class)) return;
        $path = APP_DIR."/$layer/$subdir".SInflection::underscore($class).'.php';
        if (!file_exists($path))
            throw new SException("Missing ".SInflection::singularize($layer)." $dependency");
        require_once($path);
        if ($layer == 'models') SActiveRecordMeta::add_manager_to_class(SInflection::camelize($class));
    }
    
    public static function dependency_file_path($layer, $dependency, $relative_to = null)
    {
        list($subdir, $class) = self::dependency_sub_dir($dependency, $relative_to);
        return APP_DIR."/$layer/$subdir".SInflection::underscore($class).'.php';
    }
    
    public static function sub_directory($relative_to)
    {
        if ($relative_to === null) return '';
        
        $reflection = new ReflectionClass($relative_to);
        return self::file_sub_directory($reflection->getFileName());
    }
    
    public static function file_sub_directory($file)
    {
        $relative_dir = preg_replace('#'.APP_DIR.'/(\w+)/#i', '', 
                        str_replace('\\', '/', $file));
        if (strpos($relative_dir, '/') !== false)
        {
            list($subdir, $file) = explode('/', $relative_dir);
            return $subdir.'/';
        }
        return '';
    }
    
    private static function dependency_sub_dir($dependency, $relative_to)
    {
        if (strpos($dependency, '/') === false)
            return array(self::sub_directory($relative_to), $dependency);
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
