<?php

class CreateAppCommand extends SCommand
{
    protected $allowed_options = array('path' => true, 'compile' => false);
    protected $allowed_params  = array('project_name' => true);
    
    private $project_dirs      = array('app', 'cache', 'conf', 'db', 'lib', 'log', 'public', 'scripts', 'test');
    private $project_app_dirs  = array('controllers', 'helpers', 'i18n', 'models', 'views', 'resources');
    private $compilable_layers = array('common', 'gemini', 'mercury');
    
    public function execute()
    {
        if (isset($this->options['path'])) $root_path = $this->options['path'];
        else $root_path = realpath(STATO_CORE_PATH.'/..');
        
        $project_path = $root_path.'/'.$this->params['project_name'];
        
        $this->create_dir('', $project_path);
        
        foreach ($this->project_dirs as $dir) $this->create_dir($dir, $project_path);   
        foreach ($this->project_app_dirs as $dir) $this->create_dir("app/$dir", $project_path);
        
        $this->create_dir("app/views/layouts", $project_path);
        
        $this->create_dir("cache/fragments", $project_path);
        $this->create_dir("cache/templates", $project_path);
        $this->create_dir("cache/generated_code", $project_path);
        
        $this->create_dir("db/migrate", $project_path);
        
        foreach (array('fixtures', 'functional', 'mocks', 'unit') as $dir)
            $this->create_dir("test/$dir", $project_path);
        
        $this->copy(STATO_CORE_PATH.'/gemini/lib/templates/createapp/conf', 'conf', $project_path);
        $this->copy(STATO_CORE_PATH.'/gemini/lib/templates/createapp/public', 'public', $project_path);
        $this->copy(STATO_CORE_PATH.'/gemini/lib/templates/createapp/scripts', 'scripts', $project_path);
        
        foreach (array('development', 'test', 'production') as $log)
            $this->create_file("log/$log.log", $project_path);
            
        $this->create_file("app/controllers/application_controller.php", $project_path,
            SCodeGenerator::generate_class('ApplicationController', '', 'SActionController'));
        
        $this->create_file("app/helpers/application_helper.php", $project_path);
            
        if (!isset($this->options['compile'])) $project_core_path = STATO_CORE_PATH;
        else
        {
            $project_core_path = "$project_path/core";
            $this->create_dir("core", $project_path);
            $this->compile($project_core_path);
            $this->create_dir('components', $project_core_path);
            $this->create_dir('vendor', $project_core_path);
            $this->copy(STATO_CORE_PATH.'/components', 'components', $project_core_path);
            $this->copy(STATO_CORE_PATH.'/vendor', 'vendor', $project_core_path);
        }
        
        $this->create_file("conf/boot.php", $project_path,
            SCodeGenerator::generate_file(
                SCodeGenerator::render_template(STATO_CORE_PATH.'/gemini/lib/templates/createapp/boot.php',
                    array('project_core_path' => $project_core_path))
            )
        );
    }
    
    private function compile($core_path)
    {
        foreach ($this->compilable_layers as $layer)
        {
            $source_dir = STATO_CORE_PATH."/$layer/lib";
            $this->create_dir($layer, $core_path);
            $this->create_file("$layer/$layer.php", $core_path, SCodeGenerator::generate_file($this->compile_dir($source_dir)));
        }
    }
    
    private function compile_dir($source_dir, $code = '')
    {
        $it = new DirectoryIterator($source_dir);
        foreach ($it as $file)
        {
            if ($file->isDot() || $file->getFilename() == '.svn') continue;
            
            if ($file->isFile()) 
                $code.= str_replace(array('<?php', '?>'), '', file_get_contents($source_dir.'/'.$file->getFilename()));
            elseif ($file->isDir())
                $code.= $this->compile_dir($source_dir.'/'.$file->getFilename());
        }
        return $code;
    }
}

?>
