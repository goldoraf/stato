<?php

require_once dirname(__FILE__) . '/../../test/TestsHelper.php';

class UrlRewriterTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $map = new SRouteSet();
        $map->connect(':controller/:action/:id');
        SRoutes::initialize($map, false);
        
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/index.php/foo/bar';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['SCRIPT_FILENAME'] = '/path/to/www/index.php';
        $_SERVER['SERVER_NAME'] = 'test.host';
        $_SERVER['SERVER_PORT'] = 80;
        $request = new SRequest();
        
        SUrlRewriter::initialize($request);
    }
    
    public function test_basic()
    {
        $this->assertEquals('http://test.host/foo/bar',
            SUrlRewriter::url_for(array('controller' => 'foo', 'action' => 'bar')));
        $this->assertEquals('/foo/bar',
            SUrlRewriter::url_for(array('controller' => 'foo', 'action' => 'bar', 'only_path' => true)));
        $this->assertEquals('foo/bar',
            SUrlRewriter::url_for(array('controller' => 'foo', 'action' => 'bar', 'only_path' => true, 'skip_relative_url_root' => true)));
            
        SActionController::$use_relative_urls = true;
            
        $this->assertEquals('/foo/bar',
            SUrlRewriter::url_for(array('controller' => 'foo', 'action' => 'bar')));
        $this->assertEquals('/foo/bar',
            SUrlRewriter::url_for(array('controller' => 'foo', 'action' => 'bar', 'only_path' => true)));
        $this->assertEquals('foo/bar',
            SUrlRewriter::url_for(array('controller' => 'foo', 'action' => 'bar', 'only_path' => true, 'skip_relative_url_root' => true)));
    }
}

