<?php

abstract class SCommand
{
    protected $args    = array();
    protected $options = array();
    protected $params  = array();
    protected $allowed_options = array();
    protected $allowed_params  = array();
    
    public static function load($command_name, $args = null)
    {
        if ($args === null)
        {
            $args = $_SERVER['argv'];
            array_shift($args);
        }
        
        $command_file = STATO_CORE_PATH."/cli/lib/commands/$command_name.php";

        if (!file_exists($command_file))
            throw new SConsoleException("$command_name command does not exist");
            
        require($command_file);
        $command_class = SInflection::camelize($command_name).'Command';
        return new $command_class($args);
    }
    
    public function __construct($args)
    {
        $this->args = $args;
        list($this->options, $this->params) 
            = SConsoleUtils::get_options_and_params($this->args, $this->allowed_options, $this->allowed_params);
    }
    
    abstract public function execute();
    
    protected function create_dir($path, $base_path)
    {
        if (file_exists($base_path.'/'.$path))
            $this->announce("exists $path");
        else
        {
            $this->announce("create $path");
            SDir::mkdir($base_path.'/'.$path);
        }
    }
    
    protected function create_file($path, $base_path, $content = '')
    {
        if (file_exists($base_path.'/'.$path))
        {
            if ($content == file_get_contents($base_path.'/'.$path))
                $this->announce("identical $path");
            else
            {
                $this->announce("overwrite $path ? (y/n)");
                $answer = fgetc(STDIN);
                if ($answer == 'n')
                    $this->announce("skip $path");
                else
                {
                    $this->announce("force $path");
                    file_put_contents($base_path.'/'.$path, $content);
                }
            }
        }
        else
        {
            $this->announce("create $path");
            file_put_contents($base_path.'/'.$path, $content);
        }
    }
    
    protected function copy($source_path, $path, $base_path)
    {
        $this->announce("copy $path");
        SDir::copy($source_path, $base_path.'/'.$path);
    }
    
    protected function test_module_existence($subdir)
    {
        if (!$this->module_exists($subdir))
            throw new SConsoleException("Module $subdir does not exist");
    }
    
    protected function module_exists($subdir)
    {
        return (file_exists(STATO_APP_PATH."/controllers/$subdir")
             && file_exists(STATO_APP_PATH."/models/$subdir")
             && file_exists(STATO_APP_PATH."/views/$subdir")
             && file_exists(STATO_APP_PATH."/helpers/$subdir"));
    }
    
    protected function announce($message)
    {
        echo "    $message\n";
    }
}

?>
