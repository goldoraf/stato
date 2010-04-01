<?php

namespace Stato\Cli;

require_once __DIR__ . '/../TestsHelper.php';

class CommandRunnerTest extends TestCase
{
    public function setup()
    {
        CommandRunner::setCommandsBasePath(__DIR__ . '/files/commands');
    }
    
    public function testNoArguments()
    {
        $this->expectOutputRegex('/usage: stato/');
        CommandRunner::main(array());
    }
    
    public function testStatoVersion()
    {
        $this->expectOutputRegex("/stato version/");
        CommandRunner::main(array('./stato.php', '--version'));
    }
    
    public function testCommandHelp()
    {
        $help = <<<EOT
stato foo - Dummy command

This is a dummy command.

options:
  -u USERNAME, --user=USERNAME
                           greet user

EOT;
        $this->expectOutputString($help);
        CommandRunner::main(array('./stato.php', '--help', 'foo'));
    }
    
    public function testCommandHelpOnUnavailableCommand()
    {
        $this->expectOutputString("stato: 'bar' is not a stato command. See 'stato --help'.\n");
        CommandRunner::main(array('./stato.php', '--help', 'bar'));
    }
    
    public function testCommandRun()
    {
        $this->expectOutputString("    Hello raphael\n");
        CommandRunner::main(array('./stato.php', 'foo', '-u', 'raphael'));
    }
    
    public function testNamespacedCommandRun()
    {
        $this->expectOutputString("    Hello world\n");
        CommandRunner::main(array('./stato.php', 'bar:foo'));
    }
}
