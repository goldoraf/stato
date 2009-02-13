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
    
    /*public function testEmptyPathRoute()
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
    }*/
    
    public function testRouteWithRequirement()
    {
        $set = new Stato_RouteSet();
        $set->addRoute('conference/:year', array('controller' => 'confs', 'requirements' => array('year' => '\d{4}')));
        $this->assertEquals(
            array('controller'=>'confs', 'year' => 2009),
            $set->recognizePath('conference/2009')
        );
        $recognized = true;
        try { $set->recognizePath('conference/foo'); } catch (Stato_RoutingError $e) { $recognized = false; }
        $this->assertFalse($recognized);
        $recognized = true;
        try { $set->recognizePath('conference'); } catch (Stato_RoutingError $e) { $recognized = false; }
        $this->assertFalse($recognized);
    }
}