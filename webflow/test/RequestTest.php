<?php

require_once dirname(__FILE__) . '/../../test/TestsHelper.php';

class RequestTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->request = new SRequest();
    }
    
    public function test_method()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertEquals('post', $this->request->method());
        $this->assertTrue($this->request->is_post());
        $this->assertFalse($this->request->is_get());
        $this->assertFalse($this->request->is_put());
        $this->assertFalse($this->request->is_delete());
        $this->assertFalse($this->request->is_head());
    }
    
    public function test_unknown_method()
    {
        $_SERVER['REQUEST_METHOD'] = 'PATCH';
        $this->setExpectedException('SUnknownHttpMethod');
        $this->request->method();
    }
    
    public function test_request_params()
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
    
    public function test_request_params_reference()
    {
        $this->params = $this->request->params;
        $this->params['foo'] = 'bar';
        $this->assertEquals('bar', $this->params['foo']);
        $this->assertEquals('bar', $this->request->params['foo']);
    }
    
    public function test_accept_format()
    {
        $_SERVER['HTTP_ACCEPT'] = 'text/javascript';
        $this->assertEquals('js', $this->request->format());
    }
    
    public function test_get_set_request_uri()
    {
        $_SERVER['REQUEST_URI'] = '/foo/bar?k1=v1&k2=v2';
        $this->assertEquals('/foo/bar?k1=v1&k2=v2', $this->request->request_uri());
        $this->request->set_request_uri('/foo/baz');
        $this->assertEquals('/foo/baz', $this->request->request_uri());
    }
    
    public function test_get_set_base_url()
    {
        $_SERVER['REQUEST_URI'] = '/app/index.php/foo/bar';
        $_SERVER['SCRIPT_NAME'] = '/app/index.php';
        $this->assertEquals('/app/index.php', $this->request->base_url());
        $this->request->set_base_url('/app/test.php');
        $this->assertEquals('/app/test.php', $this->request->base_url());
    }
    
    public function test_base_path()
    {
        $_SERVER['REQUEST_URI'] = '/index.php/foo/bar';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['SCRIPT_FILENAME'] = '/path/to/www/index.php';
        $this->assertEquals('', $this->request->base_path());
    }
    
    public function test_base_path_with_alias()
    {
        $_SERVER['REQUEST_URI'] = '/app/index.php/foo/bar';
        $_SERVER['SCRIPT_NAME'] = '/app/index.php';
        $_SERVER['SCRIPT_FILENAME'] = '/path/to/www/app/index.php';
        $this->assertEquals('/app', $this->request->base_path());
    }
    
    public function test_path_info()
    {
        $_SERVER['REQUEST_URI'] = '/index.php/foo/bar';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['SCRIPT_FILENAME'] = '/path/to/www/index.php';
        $this->assertEquals('/foo/bar', $this->request->path_info());
    }
    
    public function test_path_info_with_uri_params()
    {
        $_SERVER['REQUEST_URI'] = '/index.php/foo/bar?k1=v1&k2=v2';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['SCRIPT_FILENAME'] = '/path/to/www/index.php';
        $this->assertEquals('/foo/bar', $this->request->path_info());
    }
    
    public function test_path_info_with_mod_rewrite()
    {
        $_SERVER['REQUEST_URI'] = '/foo/bar';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['SCRIPT_FILENAME'] = '/path/to/www/index.php';
        $this->assertEquals('', $this->request->base_url());
        $this->assertEquals('/foo/bar', $this->request->path_info());
    }
    
    public function test_path_info_with_alias_and_mod_rewrite()
    {
        $_SERVER['REQUEST_URI'] = '/app/foo/bar?k1=v1&k2=v2';
        $_SERVER['SCRIPT_NAME'] = '/app/index.php';
        $_SERVER['SCRIPT_FILENAME'] = '/path/to/www/index.php';
        $this->assertEquals('/foo/bar', $this->request->path_info());
    }
    
    public function test_request_files_with_single_file()
    {
        $_FILES = array('foo' => array(
            'name' => 'presentation.pdf',
            'type' => 'application/xpdf',
            'tmp_name' => '/tmp/phpv9ccw7',
            'error' => 0,
            'size' => 823305 
        ));
        $file = $this->request->files['foo'];
        $this->assertEquals('SUploadedFile', get_class($file));
        $this->assertEquals('presentation.pdf', $file->name);
        $this->assertEquals('application/xpdf', $file->type);
        $this->assertEquals('/tmp/phpv9ccw7', $file->tmp);
        $this->assertEquals(823305, $file->size);
        $this->assertFalse($file->error);
        $this->assertFalse($file->is_safe());
    }
    
    public function test_request_files_with_multiple_file()
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
        $this->assertEquals('SUploadedFile', get_class($files[0]));
        $this->assertEquals('presentation.pdf', $files[0]->name);
        $this->assertEquals('application/xpdf', $files[0]->type);
        $this->assertEquals('/tmp/phpv9ccw7', $files[0]->tmp);
        $this->assertEquals(823305, $files[0]->size);
        $this->assertFalse($files[0]->error);
        $this->assertFalse($files[0]->is_safe());
        $this->assertEquals('SUploadedFile', get_class($files[1]));
        $this->assertEquals('author.jpg', $files[1]->name);
    }
}
