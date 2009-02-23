<?php

require_once 'PHPUnit/Extensions/OutputTestCase.php';
require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'exception.php';
require_once 'command.php';

require_once dirname(__FILE__).'/files/commands/dummy.php';

class Stato_Cli_CommandTest extends PHPUnit_Extensions_OutputTestCase
{
    public function setup()
    {
        $this->command = new Stato_Cli_DummyCommand();
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