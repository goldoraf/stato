<?php

require_once 'PHPUnit/Extensions/OutputTestCase.php';

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'dispatcher.php';
require_once 'controller.php';
require_once 'request.php';
require_once 'response.php';
require_once 'routing.php';
require_once 'helpers/string.php';

class Stato_DispatcherTest extends PHPUnit_Extensions_OutputTestCase
{
    public function setUp()
    {
        $_SERVER['SCRIPT_NAME'] = '/app/index.php';
        $_SERVER['SCRIPT_FILENAME'] = '/path/to/www/app/index.php';
        $this->request = new Stato_Request('/app/index.php/foo');
        $this->response = new Stato_Response();
        $this->routeset = new Stato_RouteSet();
        $this->routeset->addRoute(':controller/:action/:id');
        $this->dispatcher = new Stato_Dispatcher($this->routeset);
        $this->dispatcher->addControllerDir(dirname(__FILE__).'/files');
    }
    
    public function testSimpleDispatch()
    {
        $this->expectOutputString('hello world');
        $this->dispatcher->dispatch($this->request, $this->response);
    }
}