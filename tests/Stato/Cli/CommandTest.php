<?php



require_once dirname(__FILE__) . '/../TestsHelper.php';

require_once dirname(__FILE__) . '/files/commands/Dummy.php';

class Stato_Cli_CommandTest extends Stato_Cli_TestCase
{
    public function setup()
    {
        $this->command = new Stato_Cli_Command_Dummy();
    }
    
    public function testGetHelp()
    {
        $help = <<<EOT
stato dummy - Dummy command

This is a dummy command.

options:
  -v, --verbose            make lots of noise

EOT;
        $this->assertEquals($help, $this->command->getHelp());
    }
    
    public function testAnnounce()
    {
        $this->expectOutputString("    Hello world\n");
        $this->command->runAnnounce();
    }
}