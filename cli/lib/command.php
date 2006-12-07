<?php

abstract class SCommand
{
    protected $args    = array();
    protected $options = array();
    protected $params  = array();
    protected $allowed_options = array();
    protected $allowed_params  = array();
    
    public static function load($command_name, $args)
    {
        $command_file = ROOT_DIR."/cli/commands/$command_name.php";

        if (!file_exists($command_file))
            throw new SConsoleException("$command_name command does not exist");
            
        require($command_file);
        $command_class = ucfirst($command_name).'Command';
        return new $command_class($args);
    }
    
    public function __construct($args)
    {
        $this->args = $args;
        list($this->options, $this->params) 
            = SConsoleUtils::get_options_and_params($this->args, $this->allowed_options, $this->allowed_params);
    }
    
    abstract public function execute();
}

?>
