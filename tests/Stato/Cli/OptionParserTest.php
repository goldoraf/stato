<?php



require_once dirname(__FILE__) . '/../TestsHelper.php';

class Stato_Cli_OptionParserTest extends Stato_Cli_TestCase
{
    public function setup()
    {
        $this->parser = new Stato_Cli_OptionParser();
        $this->parser->addOption('-v', '--verbose', Stato_Cli_Option::BOOLEAN, null, 'make lots of noise');
        $this->parser->addOption('-f', '--force', Stato_Cli_Option::BOOLEAN, 'forceful', 'be brutal');
        $this->parser->addOption('-p', '--path', Stato_Cli_Option::STRING, null, 'use that path');
    }
    
    public function testShortBooleanOption()
    {
        $this->assertEquals(array(array('verbose' => true), array()),
            $this->parser->parseArgs(array('-v')));
    }
    
    public function testShortBooleanOptionWithDest()
    {
        $this->assertEquals(array(array('forceful' => true), array()),
            $this->parser->parseArgs(array('-f')));
    }
    
    public function testShortBooleanOptionWithArgumentProvidedShouldThrow()
    {
        $this->setExpectedException('Stato_Cli_Exception', 'option -f doesn\'t allow an argument');
        $this->parser->parseArgs(array('-f', '/path/to/stato'));
    }
    
    public function testShortBooleanMultipleOptions()
    {
        $this->assertEquals(array(array('verbose' => true, 'forceful' => true), array()),
            $this->parser->parseArgs(array('-vf')));
    }
    
    public function testShortBooleanMultipleOptionsWithArgumentProvidedShouldThrow()
    {
        $this->setExpectedException('Stato_Cli_Exception', 'multiple options -vf can\'t be foolowed by an argument');
        $this->parser->parseArgs(array('-vf', '/path/to/stato'));
    }
    
    public function testMultipleOptionsIncludingShortStringOptionShouldThrow()
    {
        $this->setExpectedException('Stato_Cli_Exception', 'option -p requires an argument');
        $this->parser->parseArgs(array('-vp'));
    }
    
    public function testShortStringOption()
    {
        $this->assertEquals(array(array('path' => '/path/to/stato'), array()),
            $this->parser->parseArgs(array('-p', '/path/to/stato')));
    }
    
    public function testShortStringOptionWithoutArgumentProvidedShouldThrow()
    {
        $this->setExpectedException('Stato_Cli_Exception', 'option -p requires an argument');
        $this->parser->parseArgs(array('-p', '-f'));
    }
    
    public function testLongBooleanOption()
    {
        $this->assertEquals(array(array('verbose' => true), array()),
            $this->parser->parseArgs(array('--verbose')));
    }
    
    public function testLongBooleanOptionWithDest()
    {
        $this->assertEquals(array(array('forceful' => true), array()),
            $this->parser->parseArgs(array('--force')));
    }
    
    public function testLongStringOptionWithArgument()
    {
        $this->assertEquals(array(array('path' => '/path/to/stato'), array()),
            $this->parser->parseArgs(array('--path=/path/to/stato')));
    }
    
    public function testLongStringOptionWithSeparatedArgument()
    {
        $this->assertEquals(array(array('path' => '/path/to/stato'), array()),
            $this->parser->parseArgs(array('--path', '/path/to/stato')));
    }
    
    public function testLongStringOptionWithoutArgumentProvidedShouldThrow()
    {
        $this->setExpectedException('Stato_Cli_Exception', 'option --path requires an argument');
        $this->parser->parseArgs(array('--path', '-f'));
    }
    
    public function testLongBooleanOptionWithSeparatedArgumentProvidedShouldThrow()
    {
        $this->setExpectedException('Stato_Cli_Exception', 'option --force doesn\'t allow an argument');
        $this->parser->parseArgs(array('--force', '/path/to/stato'));
    }
    
    public function testLongBooleanOptionWithArgumentProvidedShouldThrow()
    {
        $this->setExpectedException('Stato_Cli_Exception', 'option --force doesn\'t allow an argument');
        $this->parser->parseArgs(array('--force=/path/to/stato'));
    }
    
    public function testNonOpts()
    {
        $this->assertEquals(array(array('path' => '/path/to/stato'), array('foo', 'bar')),
            $this->parser->parseArgs(array('--path', '/path/to/stato', 'foo', 'bar')));
    }
}