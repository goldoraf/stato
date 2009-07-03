<?php

namespace Stato\Cli\Command;

use Stato\Cli\TestCase;

require_once __DIR__ . '/../../TestsHelper.php';

class CreateappTest extends TestCase
{
    public function setup()
    {
        $this->command = new Createapp();
        $this->path = __DIR__ . '/../files/sandbox';
    }
    
    public function tearDown()
    {
        // recursive rm
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