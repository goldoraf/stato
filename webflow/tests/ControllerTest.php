<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'controller.php';
require_once 'request.php';
require_once 'response.php';

require_once 'files/foo_controller.php';

class Stato_ControllerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->request = new Stato_Request();
        $this->response = new Stato_Response();
        $this->controller = new FooController($this->request, $this->response);
        $this->controller->addViewPath(dirname(__FILE__).'/files/views');
    }
    
    public function testRun()
    {
        $this->request->setParams(array('action' => 'bar'));
        $response = $this->controller->run();
        $this->assertEquals('hello world', $response->getBody());
    }
    
    public function testRunUnexistentAction()
    {
        $this->setExpectedException('Stato_ActionNotFound');
        $this->request->setParams(array('action' => 'dummy'));
        $response = $this->controller->run();
    }
    
    public function testRenderText()
    {
        $this->controller->renderSimpleText();
        $this->assertEquals('hello world', $this->response->getBody());
        $this->assertEquals('200 OK', $this->response->getStatus());
    }
    
    public function testDoubleRenderShouldThrowAnException()
    {
        $this->controller->renderSimpleText();
        $this->setExpectedException('Stato_DoubleRenderError');
        $this->controller->renderSimpleText();
    }
    
    public function testRenderTextWithStatus()
    {
        $this->controller->renderTextWithStatus();
        $this->assertEquals('hello world', $this->response->getBody());
        $this->assertEquals('500 Internal Server Error', $this->response->getStatus());
    }
    
    public function testRenderTextWithLayout()
    {
        $this->controller->renderTextWithLayout();
        $this->assertEquals('<html><body>hello world</body></html>', $this->response->getBody());
        $this->assertEquals('200 OK', $this->response->getStatus());
    }
    
    public function testRenderSimpleFile()
    {
        $this->controller->renderSimpleFile();
        $this->assertEquals('hello world', $this->response->getBody());
        $this->assertEquals('200 OK', $this->response->getStatus());
    }
        
    public function testRenderFileWithAssigns()
    {
        $this->controller->renderFileWithAssigns();
        $this->assertEquals('hello raphael', $this->response->getBody());
    }
    
    public function testRenderMissingFile()
    {    
        $this->setExpectedException('Stato_MissingTemplate');
        $this->controller->renderMissingFile();
    }
    
    public function testRenderSimpleTemplate()
    {
        $this->controller->renderSimpleTemplate();
        $this->assertEquals('hello world', $this->response->getBody());
        $this->assertEquals('200 OK', $this->response->getStatus());
    }
    
    public function testRenderMissingTemplate()
    {
        $this->setExpectedException('Stato_MissingTemplate');
        $this->controller->renderMissingTemplate();
    }
}
