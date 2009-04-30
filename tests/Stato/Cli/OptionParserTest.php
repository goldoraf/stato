<?php

namespace Stato\Cli;

require_once dirname(__FILE__) . '/../TestsHelper.php';

class OptionParserTest extends TestCase
{
    public function setup()
    {
        $this->parser = new OptionParser();
        $this->parser->addOption('-v', '--verbose', Option::BOOLEAN, null, 'make lots of noise');
        $this->parser->addOption('-f', '--force', Option::BOOLEAN, 'forceful', 'be brutal');
        $this->parser->addOption('-p', '--path', Option::STRING, null, 'use that path');
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
        $this->setExpectedException('Stato\Cli\Exception', 'option -f doesn\'t allow an argument');
        $this->parser->parseArgs(array('-f', '/path/to/stato'));
    }
    
    public function testShortBooleanMultipleOptions()
    {
        $this->assertEquals(array(array('verbose' => true, 'forceful' => true), array()),
            $this->parser->parseArgs(array('-vf')));
    }
    
    public function testShortBooleanMultipleOptionsWithArgumentProvidedShouldThrow()
    {
        $this->setExpectedException('Stato\Cli\Exception', 'multiple options -vf can\'t be foolowed by an argument');
        $this->parser->parseArgs(array('-vf', '/path/to/stato'));
    }
    
    public function testMultipleOptionsIncludingShortStringOptionShouldThrow()
    {
        $this->setExpectedException('Stato\Cli\Exception', 'option -p requires an argument');
        $this->parser->parseArgs(array('-vp'));
    }
    
    public function testShortStringOption()
    {
        $this->assertEquals(array(array('path' => '/path/to/stato'), array()),
            $this->parser->parseArgs(array('-p', '/path/to/stato')));
    }
    
    public function testShortStringOptionWithoutArgumentProvidedShouldThrow()
    {
        $this->setExpectedException('Stato\Cli\Exception', 'option -p requires an argument');
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
        $this->setExpectedException('Stato\Cli\Exception', 'option --path requires an argument');
        $this->parser->parseArgs(array('--path', '-f'));
    }
    
    public function testLongBooleanOptionWithSeparatedArgumentProvidedShouldThrow()
    {
        $this->setExpectedException('Stato\Cli\Exception', 'option --force doesn\'t allow an argument');
        $this->parser->parseArgs(array('--force', '/path/to/stato'));
    }
    
    public function testLongBooleanOptionWithArgumentProvidedShouldThrow()
    {
        $this->setExpectedException('Stato\Cli\Exception', 'option --force doesn\'t allow an argument');
        $this->parser->parseArgs(array('--force=/path/to/stato'));
    }
    
    public function testNonOpts()
    {
        $this->assertEquals(array(array('path' => '/path/to/stato'), array('foo', 'bar')),
            $this->parser->parseArgs(array('--path', '/path/to/stato', 'foo', 'bar')));
    }
}