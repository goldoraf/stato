<?php

namespace Stato\Webflow;

require_once __DIR__ . '/../TestsHelper.php';

class RequestTest extends TestCase
{
    private $request;
    
    public function setUp()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->request = new Request();
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
        $this->setExpectedException(__NAMESPACE__ . '\InvalidHttpMethod');
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
        $this->assertEquals('/', $this->request->getBasePath());
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
    
    public function testRequestFilesWithSingleFile()
    {
        $_FILES = array('foo' => array(
            'name' => 'presentation.pdf',
            'type' => 'application/xpdf',
            'tmp_name' => '/tmp/phpv9ccw7',
            'error' => 0,
            'size' => 823305 
        ));
        $file = $this->request->files['foo'];
        $this->assertTrue($file instanceof UploadedFile);
        $this->assertEquals('presentation.pdf', $file->name);
        $this->assertEquals('application/xpdf', $file->type);
        $this->assertEquals('/tmp/phpv9ccw7', $file->tmp);
        $this->assertEquals(823305, $file->size);
        $this->assertFalse($file->error);
        $this->assertFalse($file->isSafe());
    }
    
    public function testRequestFilesWithMultipleFile()
    {
        $_FILES = array('foo' => array(
            'name' => array('presentation.pdf', 'author.jpg'),
            'type' => array('application/xpdf', 'image/jpeg'),
            'tmp_name' => array('/tmp/phpv9ccw7', '/tmp/phpXXN67y'),
            'error' => array(0, 0),
            'size' => array(823305, 1868562) 
        ));
        $files = $this->request->files['foo'];
        $this->assertTrue(is_array($files));
        $this->assertTrue($files[0] instanceof UploadedFile);
        $this->assertEquals('presentation.pdf', $files[0]->name);
        $this->assertEquals('application/xpdf', $files[0]->type);
        $this->assertEquals('/tmp/phpv9ccw7', $files[0]->tmp);
        $this->assertEquals(823305, $files[0]->size);
        $this->assertFalse($files[0]->error);
        $this->assertFalse($files[0]->isSafe());
        $this->assertTrue($files[1] instanceof UploadedFile);
        $this->assertEquals('author.jpg', $files[1]->name);
    }
}
