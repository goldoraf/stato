<?php

namespace Stato\Webflow;

require_once __DIR__ . '/../TestsHelper.php';

require_once 'PHPUnit/Extensions/OutputTestCase.php';

require_once __DIR__ . '/files/plugins/FooPlugin.php';
require_once __DIR__ . '/files/plugins/BarPlugin.php';

class DispatcherTest extends \PHPUnit_Extensions_OutputTestCase
{
    public function setUp()
    {
        $_SERVER['SCRIPT_NAME'] = '/app/index.php';
        $_SERVER['SCRIPT_FILENAME'] = '/path/to/www/app/index.php';
        $this->request = new Request();
        $this->response = new Response();
        $this->routeset = new RouteSet();
        $this->routeset->addRoute(':controller/:action/:id');
        $this->dispatcher = new Dispatcher($this->routeset);
        $this->dispatcher->addControllerDir(__DIR__ . '/files/controllers');
    }
    
    public function testSimpleDispatch()
    {
        $this->expectOutputString('hello world');
        $this->request->setRequestUri('/app/index.php/foo');
        $this->dispatcher->dispatch($this->request, $this->response);
    }
    
    public function testDispatchWithFileRendering()
    {
        $this->expectOutputString('hello world');
        $this->request->setRequestUri('/app/index.php/foo/foo');
        $this->dispatcher->dispatch($this->request, $this->response);
    }
    
    public function testMissingControllerFile()
    {
        $this->setExpectedException('Stato\Webflow\ControllerNotFound');
        $this->request->setRequestUri('/app/index.php/test/foo');
        $this->dispatcher->dispatch($this->request, $this->response);
    }
    
    public function testMissingControllerClass()
    {
        $this->setExpectedException('Stato\Webflow\ControllerNotFound');
        $this->request->setRequestUri('/app/index.php/missing_class/foo');
        $this->dispatcher->dispatch($this->request, $this->response);
    }
    
    public function testDispatchWithPlugins()
    {
        $this->request->setRequestUri('/app/index.php/foo');
	$this->dispatcher->plugins[] = new \FooPlugin();
        $this->dispatcher->plugins[] = new \BarPlugin();
        $this->dispatcher->dispatch($this->request, $this->response);
	$this->assertEquals('Foo', $this->request->params['preRouting']);
	$this->assertEquals('Bar', $this->request->params['postRouting']);
	$this->assertEquals(array('Foo', 'Bar'), $this->request->params['preDispatch']);
    }
}
