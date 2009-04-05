<?php

class GenerateCommand extends SCommand
{
    protected $allowed_params = array('type' => true, 'name' => true);
    protected $allowed_types  = array('controller', 'resource', 'model', 'migration', 'module');
    
    public function execute()
    {
        if (!in_array($this->params['type'], $this->allowed_types))
            throw new SConsoleException('I don\'t know how to generate a '.$this->params['type']);
        
        $generate_method = 'generate_'.$this->params['type'];
        $this->$generate_method();
    }
    
    private function generate_controller()
    {
        list($path, $controller_name) = $this->path_and_name($this->params['name']);
        
        $class_name = SInflection::camelize($controller_name).'Controller';
            
        $content = SCodeGenerator::generate_class($class_name, '', 'ApplicationController');
        
        $this->create_file("{$path}/controllers/{$controller_name}_controller.php", STATO_APP_ROOT_PATH, $content);
        $this->create_dir("{$path}/views/{$controller_name}", STATO_APP_ROOT_PATH);
    }
    
    private function generate_resource()
    {
        list($path, $resource_name) = $this->path_and_name($this->params['name']);
        
        $class_name = SInflection::camelize($resource_name).'Resource';
        
        if (!is_dir(STATO_APP_ROOT_PATH."/{$path}/resources"))
            $this->create_dir("{$path}/resources", STATO_APP_ROOT_PATH);
        
        $this->create_file("{$path}/resources/{$resource_name}_resource.php", STATO_APP_ROOT_PATH, 
            SCodeGenerator::generate_file(
                SCodeGenerator::render_template(
                    STATO_CORE_PATH."/webflow/lib/templates/resource.php",
                    array('class_name' => $class_name)
                )
            )
        );
    }
    
    private function generate_model()
    {
        list($path, $model_name) = $this->path_and_name($this->params['name']);
        
        $class_name = SInflection::camelize($model_name);
            
        $content = SCodeGenerator::generate_class($class_name, '    public static $objects;', 'SActiveRecord');
        
        $this->create_file("{$path}/models/{$model_name}.php", STATO_APP_ROOT_PATH, $content);
        
        $table_name = SInflection::pluralize($model_name);
        $migration_name = 'create_'.$table_name;
        
        if ($this->ask("generate a migration file ?"))
            $this->generate_migration($migration_name, $table_name);
    }
    
    private function generate_migration($migration_name = null, $table_name = null)
    {
        if ($migration_name === null)
            $migration_name = $this->params['name'];
        
        $migration_class = SInflection::camelize($migration_name);
        $migration_version = SMigrator::last_version(STATO_APP_ROOT_PATH.'/db/migrate') + 1;
        $migration_path = "db/migrate/{$migration_version}_{$migration_name}.php";
        
        if ($table_name === null) {
            $template = STATO_CORE_PATH."/webflow/lib/templates/empty_migration.php";
            $assigns = array('class_name' => $migration_class);
        } else {
            $template = STATO_CORE_PATH."/webflow/lib/templates/migration.php";
            $assigns = array('table_name' => $table_name, 'class_name' => $migration_class);
        }
        
        $this->create_file($migration_path, STATO_APP_ROOT_PATH,
            SCodeGenerator::generate_file(
                SCodeGenerator::render_template($template, $assigns)
            )
        );
    }
    
    private function generate_module()
    {
        $module_name = $this->params['name'];
        
        if ($this->module_exists($module_name))
            die("Module $module_name already exists.\n");
            
        if (!is_dir(STATO_APP_ROOT_PATH.'/modules'))
            $this->create_dir('modules', STATO_APP_ROOT_PATH);
            
        $this->create_dir("modules/$module_name", STATO_APP_ROOT_PATH);
        $this->create_dir("modules/$module_name/controllers", STATO_APP_ROOT_PATH);
        $this->create_dir("modules/$module_name/models", STATO_APP_ROOT_PATH);
        $this->create_dir("modules/$module_name/views", STATO_APP_ROOT_PATH);
        $this->create_dir("modules/$module_name/helpers", STATO_APP_ROOT_PATH);
        
        $this->create_file("modules/$module_name/controllers/base_controller.php", STATO_APP_ROOT_PATH,
            SCodeGenerator::generate_class(SInflection::camelize($module_name).'BaseController', '', 'ApplicationController'));
    }
    
    private function path_and_name($name)
    {
        if (strpos($name, '::') !== false)
            list($module, $name) = explode('::', $name);
        
        if (empty($module)) $path = 'app';
        else
        {
            $this->test_module_existence($module);
            $path = "modules/$module";
        }
        
        return array($path, $name);
    }
}

?>
