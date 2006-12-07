<?php

require_once(ROOT_DIR.'/model/lib/filesystem/dir.php');

class CreateAppCommand extends SCommand
{
    protected $allowed_options = array('path' => true);
    protected $allowed_params  = array('project_name' => true);
    
    public function execute()
    {
        if (isset($this->options['path'])) $root_path = $this->options['path'];
        else $root_path = realpath(ROOT_DIR.'/..');
        
        $project_path = $root_path.'/'.$this->params['project_name'];
        SDir::mkdir($project_path);
        foreach (array('app', 'cache', 'conf', 'db', 'lib', 'log', 'public', 'scripts') as $dir)
            SDir::mkdir("$project_path/$dir");
            
        foreach (array('controllers', 'helpers', 'i18n', 'models', 'views') as $dir)
            SDir::mkdir("$project_path/app/$dir");
        
        SDir::mkdir("$project_path/app/views/layouts");
        
        SDir::mkdir("$project_path/cache/fragments");
        SDir::mkdir("$project_path/cache/templates");
        
        SDir::mkdir("$project_path/db/migrate");
        
        SDir::copy(ROOT_DIR.'/build/conf', "$project_path/conf");
        SDir::copy(ROOT_DIR.'/build/public', "$project_path/public");
        
        foreach (array('development', 'test', 'production') as $log)
            file_put_contents("$project_path/log/$log.log", '');
            
        file_put_contents("$project_path/app/controllers/application_controller.php",
            SCodeGenerator::generate_class('ApplicationController', '', 'SActionController'));
            
        file_put_contents("$project_path/conf/environment.php",
            "<?php\n\ndefine('APP_MODE', 'dev');\ndefine('CORE_DIR', '".ROOT_DIR."');\n\n?>");
    }
}

?>
