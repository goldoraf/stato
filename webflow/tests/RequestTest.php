<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'request.php';

class Stato_RequestTest extends PHPUnit_Framework_TestCase
{
    private $request;
    
    public function setUp()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->request = new Stato_Request();
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
    
    public function testInvalidMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'PATCH';
        $this->setExpectedException('Stato_InvalidHttpMethod');
        $this->request->getMethod();
    }
    
    public function testRequestParams()
    {
        $_GET = array('foo' => 'bar');
        $this->assertEquals('bar', $this->request->params['foo']);
        $_POST = array('foo' => 'baz');
        $this->assertEquals('bar', $this->request->params['foo']);
        $this->request->params['foo'] = 'baz';
        $this->assertEquals('baz', $this->request->params['foo']);
        $this->request->params->merge(array('foo' => 'bat', 'hello' => 'world'));
        $this->assertEquals('bat', $this->request->params['foo']);
        $this->assertEquals('world', $this->request->params['hello']);
    }
    
    public function testRequestParamsReference()
    {
        $this->params = $this->request->params;
        $this->params['foo'] = 'bar';
        $this->assertEquals('bar', $this->params['foo']);
        $this->assertEquals('bar', $this->request->params['foo']);
    }
    
    public function testGetParam()
    {
        $_GET = array('foo' => 'bar');
        $this->assertEquals('bar', $this->request->getParam('foo'));
        $_POST = array('foo' => 'baz');
        $this->assertEquals('bar', $this->request->getParam('foo'));
        $this->request->setParams(array('foo' => 'baz'));
        $this->assertEquals('baz', $this->request->getParam('foo'));
    }
    
    public function testGetSetRequestUri()
    {
        $_SERVER['REQUEST_URI'] = '/foo/bar?k1=v1&k2=v2';
        $this->assertEquals('/foo/bar?k1=v1&k2=v2', $this->request->getRequestUri());
        $this->request->setRequestUri('/foo/baz');
        $this->assertEquals('/foo/baz', $this->request->getRequestUri());
    }
    
    public function testGetSetBaseUrl()
    {
        $_SERVER['REQUEST_URI'] = '/app/index.php/foo/bar';
        $_SERVER['SCRIPT_NAME'] = '/app/index.php';
        $this->assertEquals('/app/index.php', $this->request->getBaseUrl());
        $this->request->setBaseUrl('/app/test.php');
        $this->assertEquals('/app/test.php', $this->request->getBaseUrl());
    }
    
    public function testGetBasePath()
    {
        $_SERVER['REQUEST_URI'] = '/index.php/foo/bar';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['SCRIPT_FILENAME'] = '/path/to/www/index.php';
        $this->assertEquals('', $this->request->getBasePath());
    }
    
    public function testGetBasePathWithAlias()
    {
        $_SERVER['REQUEST_URI'] = '/app/index.php/foo/bar';
        $_SERVER['SCRIPT_NAME'] = '/app/index.php';
        $_SERVER['SCRIPT_FILENAME'] = '/path/to/www/app/index.php';
        $this->assertEquals('/app', $this->request->getBasePath());
    }
    
    public function testGetPathInfo()
    {
        $_SERVER['REQUEST_URI'] = '/index.php/foo/bar';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['SCRIPT_FILENAME'] = '/path/to/www/index.php';
        $this->assertEquals('/foo/bar', $this->request->getPathInfo());
    }
    
    public function testGetPathInfoWithUriParams()
    {
        $_SERVER['REQUEST_URI'] = '/index.php/foo/bar?k1=v1&k2=v2';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['SCRIPT_FILENAME'] = '/path/to/www/index.php';
        $this->assertEquals('/foo/bar', $this->request->getPathInfo());
    }
    
    public function testGetPathInfoWithModRewrite()
    {
        $_SERVER['REQUEST_URI'] = '/foo/bar';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['SCRIPT_FILENAME'] = '/path/to/www/index.php';
        $this->assertEquals('', $this->request->getBaseUrl());
        $this->assertEquals('/foo/bar', $this->request->getPathInfo());
    }
    
    public function testGetPathInfoWithAliasAndModRewrite()
    {
        $_SERVER['REQUEST_URI'] = '/app/foo/bar?k1=v1&k2=v2';
        $_SERVER['SCRIPT_NAME'] = '/app/index.php';
        $_SERVER['SCRIPT_FILENAME'] = '/path/to/www/index.php';
        $this->assertEquals('/foo/bar', $this->request->getPathInfo());
    }
}
