<?php

namespace Stato\Webflow;

require_once __DIR__ . '/../TestsHelper.php';

class FlashTest extends TestCase
{
    private $flash;
    
    public function setUp()
    {
        $this->flash = new Flash();
    }
    
    public function testVariableAssigns()
    {
        $this->flash['foo'] = 'bar';
        $this->flash['hello'] = array();
        $this->flash['hello']['world'] = 'php';
        $this->assertEquals('bar', $this->flash['foo']);
        $this->assertEquals('php', $this->flash['hello']['world']);
    }
    
    public function testDiscard()
    {
        $this->flash['notice'] = 'Hello world';
        $this->flash['warning'] = 'DANGEROUS !';
        $this->flash->discard();
        $this->assertEquals('Hello world', $this->flash['notice']);
        $this->assertEquals('DANGEROUS !', $this->flash['warning']);
        $this->flash->discard();
        $this->assertFalse(isset($this->flash['notice']));
        $this->assertFalse(isset($this->flash['warning']));
    }
    
    public function testKeep()
    {
        $this->flash['notice'] = 'Hello world';
        $this->flash['warning'] = 'DANGEROUS !';
        $this->flash->discard();
        $this->assertEquals('Hello world', $this->flash['notice']);
        $this->assertEquals('DANGEROUS !', $this->flash['warning']);
        $this->flash->keep('notice');
        $this->flash->discard();
        $this->assertTrue(isset($this->flash['notice']));
        $this->assertFalse(isset($this->flash['warning']));
    }
}