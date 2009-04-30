<?php

namespace Stato\Cli;

class CommandRunner
{
    private static $version = '2.0b1';
    
    private static $commandsBasePath = null;
    
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
            $parser = new OptionParser(self::getOptions());
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
            if (!$command) {
                echo("stato: '{$commandName}' is not a stato command. See 'stato --help'.\n");
                return;
            }
            $parser = new OptionParser($command->getOptions());
            list($opts, $nonOpts) = $parser->parseArgs($args);
            $command->run($opts, $nonOpts);
        }
    }
    
    public static function setCommandsBasePath($commandsBasePath)
    {
        self::$commandsBasePath = $commandsBasePath;
    }
    
    private static function getCommand($commandName)
    {
        $commandClass = self::getCommandClass($commandName);
        $commandFile = self::getCommandPath($commandName);
        if (!file_exists($commandFile)) return false;
        require_once $commandFile;
        if (!class_exists($commandClass, false)) return false;
        return new $commandClass;
    }
    
    private static function getOptions()
    {
        return array(
            new Option('-v', '--version', Option::BOOLEAN),
            new Option('-h', '--help', Option::STRING)
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
    
    private static function getCommandPath($commandName)
    {
        if (self::$commandsBasePath === null) self::$commandsBasePath = __DIR__ . '/Command';
        $commandPath = self::$commandsBasePath;
        foreach (explode(':', $commandName) as $segment) $commandPath.= '/'.ucfirst($segment);
        return $commandPath.'.php';
    }
    
    private static function getCommandClass($commandName)
    {
        $commandClass = __NAMESPACE__ . '\Command';
        foreach (explode(':', $commandName) as $segment) $commandClass.= '\\'.ucfirst($segment);
        return $commandClass;
    }
}