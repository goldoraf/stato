<?php

class UrlRewriterTest extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $map = new SRouteSet();
        $map->connect(':controller/:action/:id');
        SRoutes::initialize($map, false);
        
        SUrlRewriter::initialize(new MockRequest());
    }
    
    function test_basic()
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

?>
