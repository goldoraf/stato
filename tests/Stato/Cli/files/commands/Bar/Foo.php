<?php






class Stato_Cli_Command_Bar_Foo extends Stato_Cli_Command
{
    public function __construct()
    {
        parent::__construct();
        $this->shortDesc = 'stato foo - Dummy command';
        $this->longDesc = 'This is a dummy command.';
    }
    
    public function run($options = array(), $args = array())
    {
        $this->announce("Hello world");
    }
}