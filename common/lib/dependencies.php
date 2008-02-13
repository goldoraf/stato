<?php

spl_autoload_register(array('SDependencies', 'auto_require_model'));

class SDependencyNotFound extends Exception {}

class SDependencies
{
    private static $modules = null;
    private static $loaded_components = array();
    
    public static function auto_require_model($class_name)
    {
        try {
            self::require_model($class_name);
        } catch (SDependencyNotFound $e) {
            // we don't throw an exception because __autoload forbids to catch it
            // and instead returns a fatal error. Triggering an error will allow 
            // our error handler to convert into a proper exception.
            trigger_error("Missing $class_name model", E_USER_ERROR);   
        }
    }
    
    public static function require_models($models)
    {
        foreach ($models as $model) self::require_model($model);
    }
    
    public static function require_model($model, $camelize = true)
    {
        $class_name = ($camelize) ? SInflection::camelize($model) : $model;
        if (($complete_path = self::model_exists($class_name)) === false)
            throw new SDependencyNotFound("Missing $class_name model");
        
        require_once($complete_path);
        
        if (self::descends_from_active_record($class_name))
            SMapper::add_manager_to_class($class_name);
    }
    
    public static function model_exists($class_name)
    {
        $file_name = SInflection::underscore($class_name).'.php';
        $possible_paths = array("/app/models/$file_name");
        
        foreach (self::modules_list() as $module)
            $possible_paths[] = "/modules/$module/models/$file_name";
        
        foreach ($possible_paths as $path)
            if (file_exists(STATO_APP_ROOT_PATH.$path))
                return STATO_APP_ROOT_PATH.$path;
        
        return false;
    }
    
    public static function require_helpers($helpers, $module = null)
    {
        foreach ($helpers as $helper) self::require_helper($helper, $module);
    }
    
    public static function require_helper($helper, $module = null)
    {
        if ($module === null) $path = STATO_APP_PATH."/helpers/{$helper}_helper.php";
        else $path = STATO_APP_ROOT_PATH."/modules/{$module}/helpers/{$helper}_helper.php";
        
        if (!file_exists($path))
            throw new SDependencyNotFound("Missing $helper helper");
        
        require_once($path);
    }
    
    public static function require_components($components)
    {
        foreach ($components as $component) self::require_component($component);
    }
    
    public static function require_component($component)
    {
        $path = STATO_CORE_PATH."/components/$component/$component.php";
        if (!file_exists($path))
            throw new SDependencyNotFound("Missing component $component");
        
        self::$loaded_components[] = $component;
        require_once($path);
    }
    
    public static function is_loaded_component($component)
    {
        return in_array($component, self::$loaded_components);
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
    
    private static function modules_list()
    {
        if (self::$modules !== null) return self::$modules;
        
        self::$modules = array();
        if (is_dir(STATO_APP_ROOT_PATH.'/modules'))
        {
            $dir = new DirectoryIterator(STATO_APP_ROOT_PATH.'/modules');
            foreach ($dir as $file)
            {
                if ($file->isFile() || $file->isDot() || $file->getFilename() == '.svn') continue;
                elseif ($file->isDir()) self::$modules[] = $file->getFilename();
            }   
        }
        return self::$modules;
    }
}

?>
