<?php

class Stato_Cli_CommandRunner
{
    private static $version = '2.0b1';
    
    private static $commandClassPath = null;
    
    public static function main($args)
    {
        self::handleArguments($args);
    }
    
    private static function handleArguments($args)
    {
        $scriptName = array_shift($args);
        if (count($args) == 0) {
            echo self::getHelp();
        } elseif ($args[0]{0} == '-') {
            $parser = new Stato_Cli_OptionParser(self::getOptions());
            list($opts, $nonOpts) = $parser->parseArgs($args);
            if (isset($opts['version'])) 
                echo 'stato version '.self::$version."\n";
            if (isset($opts['help'])) {
                $command = self::getCommand($opts['help']);
                if (!$command) echo("stato: '{$opts['help']}' is not a stato command. See 'stato --help'.\n");
                else echo $command->getHelp();
            }
        } else {
            $commandName = array_shift($args);
            $command = self::getCommand($commandName);
            if (!$command) echo("stato: '{$commandName}' is not a stato command. See 'stato --help'.\n");
            $parser = new Stato_Cli_OptionParser($command->getOptions());
            list($opts, $nonOpts) = $parser->parseArgs($args);
            $command->run($opts, $nonOpts);
        }
    }
    
    public static function setCommandClassPath($commandClassPath)
    {
        self::$commandClassPath = $commandClassPath;
    }
    
    private static function getCommand($commandName)
    {
        $commandFile = self::getCommandClassPath()."/{$commandName}.php";
        if (!file_exists($commandFile)) return false;
        require_once $commandFile;
        $commandClass = 'Stato_Cli_'.ucfirst($commandName).'Command';
        return new $commandClass;
    }
    
    private static function getOptions()
    {
        return array(
            new Stato_Cli_Option('-v', '--version', Stato_Cli_Option::BOOLEAN),
            new Stato_Cli_Option('-h', '--help', Stato_Cli_Option::STRING)
        );
    }
    
    private static function getHelp()
    {
        $help = <<<EOT
usage: stato [-v|--version] [-h|--help] COMMAND [ARGS]

The most commonly used stato commands are:
   createapp   Create a skeleton application
   migrate     Migrate the application db to a specific version
   
See 'stato help COMMAND' for more information on a specific command.

EOT;
        return $help;
    }
    
    private static function getCommandClassPath()
    {
        if (self::$commandClassPath === null) return dirname(__FILE__).'/commands';
        return self::$commandClassPath;
    }
}