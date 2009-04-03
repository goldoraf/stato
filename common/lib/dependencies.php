<?php

spl_autoload_register(array('SDependencies', 'auto_require_model'));

class SDependencyNotFound extends Exception {}

class SDependencies
{
    private static $model_possible_paths = null;
    
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
        
        self::init_model($class_name);
    }
    
    public static function model_exists($class_name)
    {
        $file_name = SInflection::underscore($class_name).'.php';
        
        foreach (self::model_possible_paths() as $path)
            if (file_exists("{$path}/{$file_name}")) return "{$path}/{$file_name}";
        
        return false;
    }
    
    public static function init_model($class_name)
    {
        if (self::descends_from_active_record($class_name))
            SMapper::add_manager_to_class($class_name);
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
    
    public static function use_modules($modules)
    {
        foreach ($modules as $module)
        {
            $module_path = STATO_CORE_PATH."/modules/{$module}";
            if (!is_dir($module_path))
                throw new Exception("Module $module not found");
            
            if (file_exists($path = "{$module_path}/init.php")) require $path;  
        }
          
        SActionController::$installed_modules = $modules;
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
    
    private static function model_possible_paths()
    {
        if (self::$model_possible_paths !== null) return self::$model_possible_paths;
        
        self::$model_possible_paths = array(STATO_APP_ROOT_PATH.'/app/models');
        foreach (self::user_modules_list() as $module)
            self::$model_possible_paths[] = STATO_APP_ROOT_PATH."/modules/$module/models";
        foreach (SActionController::$installed_modules as $module)
            self::$model_possible_paths[] = STATO_CORE_PATH."/modules/$module/models";
            
        return self::$model_possible_paths;
    }
    
    private static function user_modules_list()
    {
        $modules = array();
        if (is_dir(STATO_APP_ROOT_PATH.'/modules'))
        {
            $dir = new DirectoryIterator(STATO_APP_ROOT_PATH.'/modules');
            foreach ($dir as $file)
            {
                if ($file->isFile() || $file->isDot() || $file->getFilename() == '.svn') continue;
                elseif ($file->isDir()) $modules[] = $file->getFilename();
            }   
        }
        return $modules;
    }
}

?>
