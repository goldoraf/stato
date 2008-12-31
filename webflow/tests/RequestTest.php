<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'request.php';

class Stato_RequestTest extends PHPUnit_Framework_TestCase
{
    private $request;
    
    public function setUp()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->request = new Stato_Request('/app/index.php/foo/bar?k1=v1&k2=v2');
    }
    
    public function testMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertEquals('post', $this->request->getMethod());
        $this->assertTrue($this->request->isPost());
        $this->assertFalse($this->request->isGet());
        $this->assertFalse($this->request->isPut());
        $this->assertFalse($this->request->isDelete());
        $this->assertFalse($this->request->isHead());
        $this->assertFalse($this->request->isOptions());
    }
    
    public function testGetParam()
    {
        $_GET = array('foo' => 'bar');
        $this->assertEquals('bar', $this->request->getParam('foo'));
        $_POST = array('foo' => 'baz');
        $this->assertEquals('bar', $this->request->getParam('foo'));
        $this->request->setParams(array('foo' => 'baz'));
        $this->assertEquals('baz', $this->request->getParam('foo'));
        $this->assertEquals('baz', $this->request->foo);
    }
    
    public function testGetRequestUri()
    {
        $this->assertEquals('/app/index.php/foo/bar?k1=v1&k2=v2', $this->request->getRequestUri());
        $this->request->setRequestUri('/app/test.php/foo/baz');
        $this->assertEquals('/app/test.php/foo/baz', $this->request->getRequestUri());
    }
    
    public function testGetBaseUrl()
    {
        $_SERVER['SCRIPT_NAME'] = '/app/index.php';
        $this->assertEquals('/app/index.php', $this->request->getBaseUrl());
        $this->request->setBaseUrl('/app/test.php');
        $this->assertEquals('/app/test.php', $this->request->getBaseUrl());
    }
    
    public function testGetBasePath()
    {
        $_SERVER['SCRIPT_NAME'] = '/app/index.php';
        $_SERVER['SCRIPT_FILENAME'] = '/path/to/www/app/index.php';
        $this->assertEquals('/app', $this->request->getBasePath());
    }
    
    public function testGetPathInfo()
    {
        $_SERVER['SCRIPT_NAME'] = '/app/index.php';
        $_SERVER['SCRIPT_FILENAME'] = '/path/to/www/app/index.php';
        $this->assertEquals('foo/bar', $this->request->getPathInfo());
    }
}
