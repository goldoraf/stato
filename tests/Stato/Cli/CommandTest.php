<?php

namespace Stato\Cli;

require_once __DIR__ . '/../TestsHelper.php';

require_once __DIR__ . '/files/commands/Dummy.php';

class CommandTest extends TestCase
{
    public function setup()
    {
        $this->command = new Command\Dummy();
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