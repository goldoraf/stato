<?php





require_once dirname(__FILE__) . '/../../TestsHelper.php';

class Stato_Cli_Command_MakemessagesTest extends Stato_Cli_TestCase
{
    public function setup()
    {
        $this->command = new Stato_Cli_Command_I18n_Makemessages();
        $this->path = dirname(__FILE__) . '/../files';
        $this->langFilePath = dirname(__FILE__) . '/../files/locale/en.php';
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