<?php

class UrlRewriterTest extends UnitTestCase
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
        $this->assertEqual('http://test.host/foo/bar',
            SUrlRewriter::url_for(array('controller' => 'foo', 'action' => 'bar')));
        $this->assertEqual('/foo/bar',
            SUrlRewriter::url_for(array('controller' => 'foo', 'action' => 'bar', 'only_path' => true)));
        $this->assertEqual('foo/bar',
            SUrlRewriter::url_for(array('controller' => 'foo', 'action' => 'bar', 'only_path' => true, 'skip_relative_url_root' => true)));
            
        SActionController::$use_relative_urls = true;
            
        $this->assertEqual('/foo/bar',
            SUrlRewriter::url_for(array('controller' => 'foo', 'action' => 'bar')));
        $this->assertEqual('/foo/bar',
            SUrlRewriter::url_for(array('controller' => 'foo', 'action' => 'bar', 'only_path' => true)));
        $this->assertEqual('foo/bar',
            SUrlRewriter::url_for(array('controller' => 'foo', 'action' => 'bar', 'only_path' => true, 'skip_relative_url_root' => true)));
    }
}

?>
