<?php

class GenerateCommand extends SCommand
{
    protected $allowed_params = array('type' => true, 'name' => true);
    protected $allowed_types  = array('controller', 'model', 'module');
    
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
        
        if (empty($subdir)) $path = STATO_APP_PATH."/models/$file_name.php";
        else
        {
            $this->test_module_existence($subdir);
            $path = STATO_APP_PATH."/models/$subdir/$file_name.php";
        }
        
        $class_name = SInflection::camelize($file_name);
            
        $content = SCodeGenerator::generate_class($class_name, '    public static $objects;', 'SActiveRecord');
        
        $this->test_file_existence($path);
        file_put_contents($path, $content);
        
        $table_name = SInflection::pluralize($file_name);
        $migration_name = 'create_'.$table_name;
        $migration_class = SInflection::camelize($migration_name);
        $migration_version = SMigrator::last_version(STATO_APP_ROOT_PATH.'/db/migrate') + 1;
        $migration_path = STATO_APP_ROOT_PATH."/db/migrate/{$migration_version}_{$migration_name}.php";
        
        $this->test_file_existence($migration_path);
        file_put_contents($migration_path, 
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
            $path = STATO_APP_PATH."/controllers/$subdir/$file_name.php";
            $views_path = STATO_APP_PATH."/views/$subdir/$views_dir";
        }
        else
        {
            $path = STATO_APP_PATH."/controllers/$file_name.php";
            $views_path = STATO_APP_PATH."/views/$views_dir";
        }
        
        $class_name = SInflection::camelize($file_name);
            
        $content = SCodeGenerator::generate_class($class_name, '', 'ApplicationController');
        
        $this->test_file_existence($path);
        $this->test_folder_existence($path);
        
        file_put_contents($path, $content);
        SDir::mkdir($views_path);
    }
    
    private function generate_module()
    {
        $module_name = $this->params['name'];
        
        if ($this->module_exists($module_name))
            die("Module $module_name already exists.");
            
        SDir::mkdir(STATO_APP_PATH."/controllers/$module_name");
        SDir::mkdir(STATO_APP_PATH."/models/$module_name");
        SDir::mkdir(STATO_APP_PATH."/views/$module_name");
        SDir::mkdir(STATO_APP_PATH."/helpers/$module_name");
    }
}

?>
