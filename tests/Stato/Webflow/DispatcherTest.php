<?php



require_once dirname(__FILE__) . '/../TestsHelper.php';

require_once 'PHPUnit/Extensions/OutputTestCase.php';

class Stato_Webflow_DispatcherTest extends PHPUnit_Extensions_OutputTestCase
{
    public function setUp()
    {
        $_SERVER['SCRIPT_NAME'] = '/app/index.php';
        $_SERVER['SCRIPT_FILENAME'] = '/path/to/www/app/index.php';
        $this->request = new Stato_Webflow_Request();
        $this->response = new Stato_Webflow_Response();
        $this->routeset = new Stato_Webflow_RouteSet();
        $this->routeset->addRoute(':controller/:action/:id');
        $this->dispatcher = new Stato_Webflow_Dispatcher($this->routeset);
        $this->dispatcher->addControllerDir(dirname(__FILE__) . '/files/controllers');
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
        $this->setExpectedException('Stato_Webflow_ControllerNotFound');
        $this->request->setRequestUri('/app/index.php/test/foo');
        $this->dispatcher->dispatch($this->request, $this->response);
    }
    
    public function testMissingControllerClass()
    {
        $this->setExpectedException('Stato_Webflow_ControllerNotFound');
        $this->request->setRequestUri('/app/index.php/missing_class/foo');
        $this->dispatcher->dispatch($this->request, $this->response);
    }
}