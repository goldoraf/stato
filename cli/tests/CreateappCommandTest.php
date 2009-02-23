<?php

require_once 'PHPUnit/Extensions/OutputTestCase.php';
require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'exception.php';
require_once 'command.php';
require_once 'commands/createapp.php';

class Stato_Cli_CreateappCommandTest extends PHPUnit_Extensions_OutputTestCase
{
    public function setup()
    {
        $this->command = new Stato_Cli_CreateappCommand();
        $this->path = dirname(__FILE__).'/files/sandbox';
    }
    
    public function testRun()
    {
        $this->command->run(array('path' => $this->path), array('testapp'));
        $this->assertFileExists($this->path.'/testapp');
        $this->assertFileExists($this->path.'/testapp/app');
        $this->assertFileExists($this->path.'/testapp/app/controllers');
        $this->assertFileExists($this->path.'/testapp/app/helpers');
        $this->assertFileExists($this->path.'/testapp/app/models');
        $this->assertFileExists($this->path.'/testapp/app/views');
        $this->assertFileExists($this->path.'/testapp/app/views/layout');
        $this->assertFileExists($this->path.'/testapp/cache');
        $this->assertFileExists($this->path.'/testapp/conf');
        $this->assertFileExists($this->path.'/testapp/db');
        $this->assertFileExists($this->path.'/testapp/db/migrate');
        $this->assertFileExists($this->path.'/testapp/lib');
        $this->assertFileExists($this->path.'/testapp/log');
        $this->assertFileExists($this->path.'/testapp/public');
        $this->assertFileExists($this->path.'/testapp/script');
        $this->assertFileExists($this->path.'/testapp/test');
    }
}