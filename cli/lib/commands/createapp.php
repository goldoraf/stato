<?php

require_once(STATO_CORE_PATH.'/model/lib/filesystem/dir.php');

class CreateAppCommand extends SCommand
{
    protected $allowed_options = array('path' => true);
    protected $allowed_params  = array('project_name' => true);
    
    public function execute()
    {
        if (isset($this->options['path'])) $root_path = $this->options['path'];
        else $root_path = realpath(STATO_CORE_PATH.'/..');
        
        $project_path = $root_path.'/'.$this->params['project_name'];
        
        $this->create_dir('', $project_path);
        
        foreach (array('app', 'cache', 'conf', 'db', 'lib', 'log', 'public', 'scripts') as $dir)
            $this->create_dir($dir, $project_path);
            
        foreach (array('controllers', 'helpers', 'i18n', 'models', 'views') as $dir)
            $this->create_dir("app/$dir", $project_path);
        
        $this->create_dir("app/views/layouts", $project_path);
        
        $this->create_dir("cache/fragments", $project_path);
        $this->create_dir("cache/templates", $project_path);
        
        $this->create_dir("db/migrate", $project_path);
        
        $this->copy(STATO_CORE_PATH.'/build/conf', 'conf', $project_path);
        $this->copy(STATO_CORE_PATH.'/build/public', 'public', $project_path);
        $this->copy(STATO_CORE_PATH.'/build/scripts', 'scripts', $project_path);
        
        foreach (array('development', 'test', 'production') as $log)
            $this->create_file("log/$log.log", $project_path);
            
        $this->create_file("app/controllers/application_controller.php", $project_path,
            SCodeGenerator::generate_class('ApplicationController', '', 'SActionController'));
        
        $this->create_file("app/helpers/application_helper.php", $project_path);
            
        $this->create_file("conf/boot.php", $project_path,
            SCodeGenerator::generate_file(
                SCodeGenerator::render_template(STATO_CORE_PATH.'/cli/lib/templates/boot.php')
            )
        );
        
        $this->create_file("conf/environment.php", $project_path,
            "<?php\n\ndefine('STATO_APP_MODE', 'dev');\n\n?>");
    }
}

?>
