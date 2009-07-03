<?php

namespace Stato\Cli\Command;

use Stato\Cli\TestCase;

require_once __DIR__ . '/../../TestsHelper.php';

class MakemessagesTest extends TestCase
{
    public function setup()
    {
        $this->command = new I18n\Makemessages();
        $this->path = __DIR__ . '/../files';
        $this->langFilePath = __DIR__ . '/../files/locale/en.php';
    }
    
    public function tearDown()
    {
        @unlink($this->langFilePath);
    }
    
    public function testRun()
    {
        $this->command->run(array('path' => $this->path, 'lang' => 'en'));
        $this->assertTrue(file_exists($this->langFilePath));
        $messages = include($this->langFilePath);
        $this->assertEquals(4, count($messages));
        $this->assertEquals('My title', $messages['My title']);
    }
}