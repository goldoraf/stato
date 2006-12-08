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
    
    protected function test_file_existence($path)
    {
        $this->test_existence('file', $path);
    }
    
    protected function test_folder_existence($path)
    {
        $this->test_existence('folder', $path);
    }
    
    protected function test_existence($type, $path)
    {
        if (file_exists($path))
        {
            echo "WARNING : $type $path already exists !\n"
            .'Do you want to overwrite (o), or abort (a) ? ';
            $answer = fgetc(STDIN);
            if ($answer == 'a') die("\nFile generation aborted.\n");
        }
        return true;
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
}

?>
