<?php



require_once dirname(__FILE__) . '/../TestsHelper.php';

class Stato_Cli_CommandRunnerTest extends Stato_Cli_TestCase
{
    public function setup()
    {
        Stato_Cli_CommandRunner::setCommandsBasePath(dirname(__FILE__) . '/files/commands');
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
        Stato_Cli_CommandRunner::main(array('./stato.php', '--help', 'foo'));
    }
    
    public function testCommandHelpOnUnavailableCommand()
    {
        $this->expectOutputString("stato: 'bar' is not a stato command. See 'stato --help'.\n");
        Stato_Cli_CommandRunner::main(array('./stato.php', '--help', 'bar'));
    }
    
    public function testCommandRun()
    {
        $this->expectOutputString("    Hello raphael\n");
        Stato_Cli_CommandRunner::main(array('./stato.php', 'foo', '-u', 'raphael'));
    }
    
    public function testNamespacedCommandRun()
    {
        $this->expectOutputString("    Hello world\n");
        Stato_Cli_CommandRunner::main(array('./stato.php', 'bar:foo'));
    }
}