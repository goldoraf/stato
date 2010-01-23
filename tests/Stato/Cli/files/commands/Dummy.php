<?php






class Stato_Cli_Command_Dummy extends Stato_Cli_Command
{
    public function __construct()
    {
        parent::__construct();
        $this->shortDesc = 'stato dummy - Dummy command';
        $this->longDesc = 'This is a dummy command.';
        
        $this->addOption('-v', '--verbose', Stato_Cli_Option::BOOLEAN, null, 'make lots of noise');
    }
    
    public function run($options = array(), $args = array())
    {
        
    }
    
    public function runAnnounce()
    {
        $this->announce('Hello world');
    }
}