<?php






class Stato_Cli_Command_Foo extends Stato_Cli_Command
{
    public function __construct()
    {
        parent::__construct();
        $this->shortDesc = 'stato foo - Dummy command';
        $this->longDesc = 'This is a dummy command.';
        
        $this->addOption('-u', '--user', Stato_Cli_Option::STRING, 'username', 'greet user');
    }
    
    public function run($options = array(), $args = array())
    {
        $this->announce("Hello {$options['username']}");
    }
}