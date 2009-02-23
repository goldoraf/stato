<?php

require_once 'PHPUnit/Extensions/OutputTestCase.php';
require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'exception.php';
require_once 'option_parser.php';
require_once 'command.php';
require_once 'command_runner.php';

class Stato_Cli_CommandRunnerTest extends PHPUnit_Extensions_OutputTestCase
{
    public function setup()
    {
        Stato_Cli_CommandRunner::setCommandClassPath(dirname(__FILE__).'/files/commands');
    }
    
    public function testNoArguments()
    {
        $this->expectOutputRegex('/usage: stato/');
        Stato_Cli_CommandRunner::main(array());
    }
    
    public function testStatoVersion()
    {
        $this->expectOutputRegex("/stato version/", ob_get_clean());
        Stato_Cli_CommandRunner::main(array('./stato.php', '--version'));
    }
    
    public function testStatoCommandHelp()
    {
        $help = <<<EOT
stato foo - Dummy command

This is a dummy command.

options:
  -u USERNAME, --user=USERNAME
                           greet user

EOT;
        $this->expectOutputString($help);
        Stato_Cli_CommandRunner::main(array('./stato.php', '--help', 'foo'));
    }
    
    public function testStatoCommandHelpOnUnavailableCommand()
    {
        $this->expectOutputString("stato: 'bar' is not a stato command. See 'stato --help'.\n");
        Stato_Cli_CommandRunner::main(array('./stato.php', '--help', 'bar'));
    }
    
    public function testStatoCommandRun()
    {
        $this->expectOutputString("    Hello raphael\n");
        Stato_Cli_CommandRunner::main(array('./stato.php', 'foo', '-u', 'raphael'));
    }
}