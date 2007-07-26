<?php

spl_autoload_register(array('SDependencies', 'auto_require_model'));

class SDependencyNotFound extends Exception {}

class SDependencies
{
    private static $subdirs = null;
    
    public static function auto_require_model($class_name)
    {
        if (($complete_path = self::model_exists($class_name)) !== false)
        {
            self::require_with_path('models', $class_name, $complete_path);
            return;
        }
        
        // we don't throw an exception because __autoload forbids to catch it
        // and instead returns a fatal error. Triggering an error will allow 
        // our error handler to convert into a proper exception.
        trigger_error("Missing $class_name model", E_USER_ERROR);
    }
    
    public static function model_exists($class_name)
    {
        $file_name = SInflection::underscore($class_name).'.php';
        $possible_paths = array($file_name);
        
        foreach (self::subdirs_list() as $subdir) $possible_paths[] = "$subdir/$file_name";
        
        foreach ($possible_paths as $path)
        {
            $complete_path = STATO_APP_PATH."/models/$path";
            if (file_exists($complete_path)) return $complete_path;
        }
        return false;
    }
    
    public static function require_components($components)
    {
        foreach ($components as $component)
        {
            $path = STATO_CORE_PATH."/components/$component/$component.php";
            if (!file_exists($path))
                throw new Exception("Missing component $component");
            require_once($path);
        }
    }
    
    public static function require_dependencies($layer, $dependencies, $relative_to = null)
    {
        foreach ($dependencies as $dependency) self::require_dependency($layer, $dependency, $relative_to);
    }
    
    public static function require_dependency($layer, $dependency, $relative_to = null)
    {
        list($subdir, $dependency) = self::dependency_sub_dir($dependency, $relative_to);
        
        $file_name  = SInflection::underscore($dependency);
        $class_name = SInflection::camelize($dependency);
        
        if (class_exists($class_name, false)) return;
        
        $path = STATO_APP_PATH."/$layer/{$subdir}{$file_name}.php";
        if (!file_exists($path))
            throw new SDependencyNotFound("Missing ".SInflection::singularize($layer)." $dependency");
        
        self::require_with_path($layer, $class_name, $path);
    }
    
    public static function require_with_path($layer, $class_name, $path)
    {
        require_once($path);
        
        if ($layer == 'models' && self::descends_from_active_record($class_name))
            SMapper::add_manager_to_class($class_name);
    }
    
    public static function dependency_file_path($layer, $dependency, $relative_to = null)
    {
        list($subdir, $class) = self::dependency_sub_dir($dependency, $relative_to);
        return STATO_APP_PATH."/$layer/$subdir".SInflection::underscore($class).'.php';
    }
    
    public static function sub_directory($relative_to)
    {
        if ($relative_to === null) return '';
        
        $reflection = new ReflectionClass($relative_to);
        return self::file_sub_directory($reflection->getFileName());
    }
    
    public static function file_sub_directory($file)
    {
        $relative_dir = preg_replace('#'.STATO_APP_PATH.'/(\w+)/#i', '', 
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
    
    private static function descends_from_active_record($class_name)
    {
        try {
            $ref = new ReflectionClass($class_name);
            return $ref->isSubclassOf(new ReflectionClass('SActiveRecord'));
        } catch (Exception $e) {
            return false;
        }
    }
    
    private static function subdirs_list()
    {
        if (self::$subdirs !== null) return self::$subdirs;
        
        self::$subdirs = array();
        $dir = new DirectoryIterator(STATO_APP_PATH.'/models');
        foreach ($dir as $file)
        {
            if ($file->isFile() || $file->isDot() || $file->getFilename() == '.svn') continue;
            elseif ($file->isDir()) self::$subdirs[] = $file->getFilename();
        }
        
        return self::$subdirs;
    }
}

?>
