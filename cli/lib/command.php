<?php

abstract class SCommand
{
    protected $args    = array();
    protected $options = array();
    protected $params  = array();
    protected $allowed_options = array();
    protected $allowed_params  = array();
    
    protected static $packages = array('webflow', 'orm', 'i18n');
    
    public static function find_and_execute()
    {
        $args = $_SERVER['argv'];
        array_shift($args); // skip file name
        $command_name = array_shift($args);
        return self::load($command_name, $args)->execute();
    }
    
    public static function load($command_name, $args)
    {
        if (!self::find_command_file($command_name))
            throw new SConsoleException("$command_name command does not exist");

        $command_class = SInflection::camelize($command_name).'Command';
        return new $command_class($args);
    }
    
    protected static function find_command_file($command_name)
    {
        foreach (self::$packages as $package) {
            if (file_exists($command_file = STATO_CORE_PATH."/$package/script/$command_name.php")) {
                require $command_file;
                return true;
            }
        }
        return false;
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
                if (!$this->ask("overwrite $path ?"))
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
    
    protected function test_module_existence($module)
    {
        if (!$this->module_exists($module))
            throw new SConsoleException("Module $module does not exist");
    }
    
    protected function module_exists($module)
    {
        return (file_exists(STATO_APP_ROOT_PATH."/modules/$module/controllers")
             && file_exists(STATO_APP_ROOT_PATH."/modules/$module/models")
             && file_exists(STATO_APP_ROOT_PATH."/modules/$module/views")
             && file_exists(STATO_APP_ROOT_PATH."/modules/$module/helpers"));
    }
    
    protected function announce($message)
    {
        echo "    $message\n";
    }
    
    protected function ask($message)
    {
        echo "    $message (y/n) ";
        $answer = trim(fgets(STDIN, 1024));
        return $answer == 'y';
    }
}

?>
