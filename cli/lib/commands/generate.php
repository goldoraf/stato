<?php

class GenerateCommand extends SCommand
{
    protected $allowed_params = array('type' => true, 'name' => true);
    protected $allowed_types  = array('controller', 'model', 'migration', 'module', 
                                      'webservice', 'ws_test_controller', 'mailer');
    
    public function execute()
    {
        if (!in_array($this->params['type'], $this->allowed_types))
            throw new SConsoleException('I don\'t know how to generate a '.$this->params['type']);
        
        $generate_method = 'generate_'.$this->params['type'];
        $this->$generate_method();
    }
    
    private function generate_model()
    {
        $file_name = $this->params['name'];
        
        if (strpos($file_name, '/') !== false)
            list($subdir, $file_name) = explode('/', $file_name);
        
        if (empty($subdir)) $path = "models/$file_name.php";
        else
        {
            $this->test_module_existence($subdir);
            $path = "models/$subdir/$file_name.php";
        }
        
        $class_name = SInflection::camelize($file_name);
            
        $content = SCodeGenerator::generate_class($class_name, '    public static $objects;', 'SActiveRecord');
        
        $this->create_file($path, STATO_APP_PATH, $content);
        
        $table_name = SInflection::pluralize($file_name);
        $migration_name = 'create_'.$table_name;
        $migration_class = SInflection::camelize($migration_name);
        $migration_version = SMigrator::last_version(STATO_APP_ROOT_PATH.'/db/migrate') + 1;
        $migration_path = "db/migrate/{$migration_version}_{$migration_name}.php";
        
        $this->create_file($migration_path, STATO_APP_ROOT_PATH,
            SCodeGenerator::generate_file(
                SCodeGenerator::render_template(
                    STATO_CORE_PATH."/cli/lib/templates/migration.php",
                    array('table_name' => $table_name, 'class_name' => $migration_class)
                )
            )
        );
    }
    
    private function generate_controller()
    {
        $views_dir = $this->params['name'];
        
        if (strpos($views_dir, '/') !== false)
            list($subdir, $views_dir) = explode('/', $views_dir);
            
        $file_name = $views_dir.'_controller';
        
        if (!empty($subdir))
        {
            $this->test_module_existence($subdir);
            $path = "controllers/$subdir/$file_name.php";
            $views_path = "views/$subdir/$views_dir";
        }
        else
        {
            $path = "controllers/$file_name.php";
            $views_path = "views/$views_dir";
        }
        
        $class_name = SInflection::camelize($file_name);
            
        $content = SCodeGenerator::generate_class($class_name, '', 'ApplicationController');
        
        $this->create_file($path, STATO_APP_PATH, $content);
        $this->create_dir($views_path, STATO_APP_PATH);
    }
    
    private function generate_mailer()
    {
        $file_name = $this->params['name'];
        
        if (strpos($file_name, '/') !== false)
            list($subdir, $file_name) = explode('/', $file_name);
        
        if (empty($subdir))
        {
            $path = "models/$file_name.php";
            $views_path = "views/$file_name";
        }
        else
        {
            $this->test_module_existence($subdir);
            $path = "models/$subdir/$file_name.php";
            $views_path = "views/$subdir/$file_name";
        }
        
        $class_name = SInflection::camelize($file_name);
            
        $content = SCodeGenerator::generate_class($class_name, '', 'SMailer');
        
        $this->create_file($path, STATO_APP_PATH, $content);
        $this->create_dir($views_path, STATO_APP_PATH);
    }
    
    private function generate_module()
    {
        $module_name = $this->params['name'];
        
        if ($this->module_exists($module_name))
            die("Module $module_name already exists.\n");
            
        $this->create_dir("controllers/$module_name", STATO_APP_PATH);
        $this->create_dir("models/$module_name", STATO_APP_PATH);
        $this->create_dir("views/$module_name", STATO_APP_PATH);
        $this->create_dir("helpers/$module_name", STATO_APP_PATH);
        $this->create_dir("apis/$module_name", STATO_APP_PATH);
        $this->create_dir("i18n/$module_name", STATO_APP_PATH);
    }
    
    private function generate_migration()
    {
        $migration_name = $this->params['name'];
        
        $migration_class = SInflection::camelize($migration_name);
        $migration_version = SMigrator::last_version(STATO_APP_ROOT_PATH.'/db/migrate') + 1;
        $migration_path = "db/migrate/{$migration_version}_{$migration_name}.php";
        
        $this->create_file($migration_path, STATO_APP_ROOT_PATH,
            SCodeGenerator::generate_file(
                SCodeGenerator::render_template(
                    STATO_CORE_PATH."/cli/lib/templates/empty_migration.php",
                    array('class_name' => $migration_class)
                )
            )
        );
    }
    
    private function generate_webservice()
    {
        $file_name = $this->params['name'];
        
        if (strpos($file_name, '/') !== false)
            list($subdir, $file_name) = explode('/', $file_name);
        
        if (empty($subdir)) $path = "apis/$file_name.php";
        else
        {
            $this->test_module_existence($subdir);
            $path = "apis/$subdir/$file_name.php";
        }
        
        $service_cc_name = SInflection::camelize($file_name);
        $api_class_name = $service_cc_name.'API';
        $service_class_name = $service_cc_name.'Service';
        
        $this->create_file($path, STATO_APP_PATH,
            SCodeGenerator::generate_file(
                SCodeGenerator::render_template(
                    STATO_CORE_PATH."/cli/lib/templates/webservice.php",
                    array('api_class_name' => $api_class_name, 'service_class_name' => $service_class_name)
                )
            )
        );
    }
    
    private function generate_ws_test_controller()
    {
        $views_dir = $this->params['name'];
        
        $templates_path = STATO_CORE_PATH.'/cli/lib/templates/web_services_test';
        
        if (strpos($views_dir, '/') !== false)
            list($subdir, $views_dir) = explode('/', $views_dir);
            
        $file_name = $views_dir.'_controller';
        
        if (!empty($subdir))
        {
            $this->test_module_existence($subdir);
            $controller_path = "controllers/$subdir/$file_name.php";
            $views_path = "views/$subdir/$views_dir";
        }
        else
        {
            $controller_path = "controllers/$file_name.php";
            $views_path = "views/$views_dir";
        }
        
        $this->create_file($controller_path, STATO_APP_PATH,
            SCodeGenerator::generate_file(
                SCodeGenerator::render_template("{$templates_path}/controller.php",
                    array('controller_class_name' => SInflection::camelize($file_name)))
            )
        );
        
        $this->create_dir($views_path, STATO_APP_PATH);
        
        foreach (array('index', 'invoke', 'set_params') as $view_name)
            $this->create_file("$views_path/$view_name.php", STATO_APP_PATH,
                file_get_contents("{$templates_path}/views/{$view_name}.php"));
                
        $this->create_file("views/layouts/test_ws.php", STATO_APP_PATH,
            file_get_contents("{$templates_path}/views/layout.php"));
    }
}

?>
