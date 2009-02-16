<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'routing.php';

class Stato_RouteSetTest extends PHPUnit_Framework_TestCase
{
    public function testDefaultRoute()
    {
        $set = new Stato_RouteSet();
        $set->addRoute(':controller/:action/:id');
        $this->assertEquals(
            array('controller'=>'posts', 'action'=>'view', 'id'=>45),
            $set->recognizePath('posts/view/45')
        );
        $this->assertEquals(
            array('controller'=>'posts', 'action'=>'view'),
            $set->recognizePath('posts/view')
        );
        $this->assertEquals(
            array('controller'=>'posts'),
            $set->recognizePath('posts')
        );
    }
    
    public function testDefaultRouteWithFormat()
    {
        $set = new Stato_RouteSet();
        $set->addRoute(':controller/:action/:id.:format');
        $this->assertEquals(
            array('controller'=>'posts', 'action'=>'view', 'id'=>45, 'format' => 'json'),
            $set->recognizePath('posts/view/45.json')
        );
        $this->assertEquals(
            array('controller'=>'posts', 'action'=>'view', 'id'=>45),
            $set->recognizePath('posts/view/45')
        );
        $this->assertEquals(
            array('controller'=>'posts', 'action'=>'view'),
            $set->recognizePath('posts/view')
        );
        $this->assertEquals(
            array('controller'=>'posts'),
            $set->recognizePath('posts')
        );
    }
    
    public function testDefaultRouteWithDefaultController()
    {
        $set = new Stato_RouteSet();
        $set->addRoute(':controller/:action/:id', array('controller' => 'blog'));
        $this->assertEquals(
            array('controller'=>'posts', 'action'=>'view', 'id'=>45),
            $set->recognizePath('posts/view/45')
        );
        $this->assertEquals(
            array('controller'=>'posts', 'action'=>'view'),
            $set->recognizePath('posts/view')
        );
        $this->assertEquals(
            array('controller'=>'posts'),
            $set->recognizePath('posts')
        );
        $this->assertEquals(
            array('controller'=>'blog'),
            $set->recognizePath('')
        );
    }
    
    public function testSimpleRoutes()
    {
        $set = new Stato_RouteSet();
        $set->addRoute('foo', array('controller'=>'bar'));
        $this->assertEquals(
            array('controller'=>'bar'),
            $set->recognizePath('foo')
        );
        $set = new Stato_RouteSet();
        $set->addRoute('posts/:category', array('controller'=>'posts', 'action'=>'list', 'category'=>'all'));
        $this->assertEquals(
            array('controller'=>'posts', 'action'=>'list', 'category'=>'php'),
            $set->recognizePath('posts/php')
        );
        $this->assertEquals(
            array('controller'=>'posts', 'action'=>'list', 'category'=>'all'),
            $set->recognizePath('posts')
        );
    }
    
    public function testEmptyPathRoute()
    {
        $set = new Stato_RouteSet();
        $set->addRoute('', array('controller' => 'home'));
        $set->addRoute(':controller/:action/:id');
        $this->assertEquals(
            array('controller'=>'posts', 'action'=>'view', 'id'=>45),
            $set->recognizePath('posts/view/45')
        );
        $this->assertEquals(
            array('controller'=>'posts', 'action'=>'view'),
            $set->recognizePath('posts/view')
        );
        $this->assertEquals(
            array('controller'=>'posts'),
            $set->recognizePath('posts')
        );
        $this->assertEquals(
            array('controller'=>'home'),
            $set->recognizePath('')
        );
    }
    
    public function testRouteWithRequirement()
    {
        $set = new Stato_RouteSet();
        $set->addRoute('conference/:year', array('controller' => 'confs'), array('year' => '\d{4}'));
        $this->assertEquals(
            array('controller'=>'confs', 'year' => 2009),
            $set->recognizePath('conference/2009')
        );
        $recognized = true;
        try { $set->recognizePath('conference/foo'); } catch (Stato_RoutingError $e) { $recognized = false; }
        $this->assertFalse($recognized);
        $recognized = true;
        /*try { $set->recognizePath('conference'); } catch (Stato_RoutingError $e) { $recognized = false; }
        $this->assertFalse($recognized);*/
    }
    
    public function testRouteWithSubSegments()
    {
        $set = new Stato_RouteSet();
        $set->setSegmentSeparators(array('/', '-'));
        $set->addRoute('articles/:id-:slug', array('controller' => 'articles'), array('id' => '\d+', 'slug' => '[a-zA-Z_]+'));
        $this->assertEquals(
            array('controller'=>'articles', 'id' => 45, 'slug' => 'foo_bar'),
            $set->recognizePath('articles/45-foo_bar')
        );
        $this->assertEquals(
            array('controller'=>'articles', 'id' => 45),
            $set->recognizePath('articles/45')
        );
    }
    
    public function testDateBasedRoute()
    {
        $set = new Stato_RouteSet();
        $set->addRoute('archives/:year/:month/:day', 
                       array('controller'=>'blog', 'action'=>'by_date', 'month'=>null, 'day'=>null), 
                       array('year'=>'\d{4}', 'day'=>'\d{1,2}', 'month'=>'\d{1,2}'));
        $this->assertEquals(
            array('controller'=>'blog', 'action'=>'by_date', 'year'=>2006, 'month'=>02, 'day'=>14),
            $set->recognizePath('archives/2006/02/14')
        );
        $this->assertEquals(
            array('controller'=>'blog', 'action'=>'by_date', 'year'=>2006, 'month'=>02, 'day'=>null),
            $set->recognizePath('archives/2006/02')
        );
        $this->assertEquals(
            array('controller'=>'blog', 'action'=>'by_date', 'year'=>2006, 'month'=>null, 'day'=>null),
            $set->recognizePath('archives/2006')
        );
    }
    
    public function testRouteGlobbing()
    {
        $set = new Stato_RouteSet();
        $set->addRoute('articles/:action/:id', array('controller'=>'articles'));
        $set->addRoute('downloads/*filepath', array('controller'=>'downloads', 'action' => 'send_file'));
        $set->addRoute('*path', array('controller' => 'pages', 'action' => 'view'));
        $this->assertEquals(
            array('controller'=>'articles', 'action'=>'edit', 'id'=>15),
            $set->recognizePath('articles/edit/15')
        );
        $this->assertEquals(
            array('controller'=>'downloads', 'action'=>'send_file', 'filepath'=>'pdf/my_book'),
            $set->recognizePath('downloads/pdf/my_book')
        );
        $this->assertEquals(
            array('controller'=>'pages', 'action'=>'view', 'path'=>'products/web/cms/php'),
            $set->recognizePath('products/web/cms/php')
        );
        $this->assertEquals(
            array('controller'=>'pages', 'action'=>'view'),
            $set->recognizePath('')
        );
    }
    
    public function testAddRouteSet()
    {
        $blogSet = new Stato_RouteSet();
        $blogSet->setSegmentSeparators(array('/', '-'));
        $blogSet->addRoute('posts/:id-:slug', 
                           array('controller' => 'blog', 'action' => 'view'), 
                           array('id' => '\d+', 'slug' => '[a-zA-Z_]+'));
        $blogSet->addRoute('archives/:year/:month/:day', 
                           array('controller'=>'blog', 'action'=>'by_date', 'month'=>null, 'day'=>null), 
                           array('year'=>'\d{4}', 'day'=>'\d{1,2}', 'month'=>'\d{1,2}'));
        $set = new Stato_RouteSet();
        $set->addRoute('', array('controller' => 'home'));
        $set->addRouteSet('blog/', $blogSet);
        $this->assertEquals(
            array('controller'=>'blog', 'action'=>'by_date', 'year'=>2006, 'month'=>02, 'day'=>14),
            $set->recognizePath('blog/archives/2006/02/14')
        );
        $this->assertEquals(
            array('controller'=>'blog', 'action'=>'by_date', 'year'=>2006, 'month'=>02, 'day'=>null),
            $set->recognizePath('blog/archives/2006/02')
        );
        $this->assertEquals(
            array('controller'=>'blog', 'action'=>'by_date', 'year'=>2006, 'month'=>null, 'day'=>null),
            $set->recognizePath('blog/archives/2006')
        );
        $this->assertEquals(
            array('controller'=>'blog', 'action' => 'view', 'id' => 45, 'slug' => 'foo_bar'),
            $set->recognizePath('blog/posts/45-foo_bar')
        );
        $this->assertEquals(
            array('controller'=>'blog', 'action' => 'view', 'id' => 45),
            $set->recognizePath('blog/posts/45')
        );
        $this->assertEquals(
            array('controller'=>'home'),
            $set->recognizePath('')
        );
    }
}