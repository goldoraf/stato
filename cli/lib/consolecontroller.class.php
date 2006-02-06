<?php

class ConsoleException extends Exception {}

class ConsoleController
{
    protected $arguments = array();
    
    public function __construct()
    {
        if ($argc == 1)
        {
            print("No argument provided !\n");
            exit(0);
        }
        print_r($this->readArguments());
    }
    
    /**
    * Function from PEAR::Console_Getopt.
    * Safely read the $argv PHP array across different PHP configurations.
    * Will take care on register_globals and register_argc_argv ini directives
    *
    * @access public
    * @return mixed the $argv PHP array
    */
    private function readArguments()
    {
        global $argv;
        if (!is_array($argv))
        {
            if (!@is_array($_SERVER['argv']))
            {
                if (!@is_array($GLOBALS['HTTP_SERVER_VARS']['argv']))
                    throw new ConsoleException("Could not read cmd args (register_argc_argv = Off ?)");
                
                return $GLOBALS['HTTP_SERVER_VARS']['argv'];
            }
            return $_SERVER['argv'];
        }
        return $argv;
    }
}

?>
