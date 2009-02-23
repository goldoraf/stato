<?php

class Stato_Cli_CreateappCommand extends Stato_Cli_Command
{
    public function __construct()
    {
        parent::__construct();
        $this->shortDesc = 'stato createapp - Create a skeleton application';
        $this->longDesc = 'Create a skeleton application.';
        
        $this->addOption('-p', '--path', Stato_Cli_Option::STRING);
    }
    
    public function run($options = array(), $args = array())
    {
        if (empty($args)) {
            echo "No app name specified.\n";
            return;
        }
        
        if (isset($options['path'])) $rootPath = $options['path'];
        else $rootPath = getcwd();
        
        $projetName = $args[0];
        $appPath = $rootPath.'/'.$projetName;
        
        $this->mkdir($projetName, $rootPath);
        $this->createAppDirs($appPath);
    }
    
    private function createAppDirs($appPath)
    {
        $this->mkdir('app', $appPath);
        $this->mkdir('app/controllers', $appPath);
        $this->mkdir('app/helpers', $appPath);
        $this->mkdir('app/models', $appPath);
        $this->mkdir('app/views', $appPath);
        $this->mkdir('app/views/layout', $appPath);
        $this->mkdir('cache', $appPath);
        $this->mkdir('conf', $appPath);
        $this->mkdir('db', $appPath);
        $this->mkdir('db/migrate', $appPath);
        $this->mkdir('lib', $appPath);
        $this->mkdir('log', $appPath);
        $this->mkdir('public', $appPath);
        $this->mkdir('script', $appPath);
        $this->mkdir('test', $appPath);
    }
}