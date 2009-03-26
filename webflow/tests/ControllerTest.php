<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'controller.php';
require_once 'request.php';
require_once 'response.php';
require_once 'session.php';
require_once 'filters.php';
require_once 'helpers/string.php';

require_once 'files/controllers/foo_controller.php';

class Stato_ControllerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->request = new Stato_Request();
        $this->response = new Stato_Response();
        $this->controller = new FooController($this->request, $this->response);
        $this->controller->addViewDir(dirname(__FILE__).'/files/views');
    }
    
    public function testRespond()
    {
        $this->controller->respondText();
        $this->assertEquals('hello world', $this->response->getBody());
        $this->assertEquals(200, $this->response->getStatus());
    }
    
    public function testDoubleRespond()
    {
        $this->controller->respondText();
        $this->setExpectedException('Stato_DoubleRespond');
        $this->controller->respondText();
    }
    
    public function testRespondWithStatus()
    {
        $this->controller->respondTextWithStatus();
        $this->assertEquals('hello world', $this->response->getBody());
        $this->assertEquals(500, $this->response->getStatus());
    }
    
    public function testRedirect()
    {
        $this->controller->simpleRedirect();
        $this->assertEquals('<html><body>You are being <a href="/posts/1234">redirected</a>.</body></html>', $this->response->getBody());
        $this->assertEquals(302, $this->response->getStatus());
        $this->assertEquals(array('Location' => '/posts/1234'), $this->response->getHeaders());
    }
    
    public function testRedirectPermanently()
    {
        $this->controller->redirectPermanently();
        $this->assertEquals('<html><body>You are being <a href="/posts/1234">redirected</a>.</body></html>', $this->response->getBody());
        $this->assertEquals(301, $this->response->getStatus());
        $this->assertEquals(array('Location' => '/posts/1234'), $this->response->getHeaders());
    }
    
    public function testDoubleRedirect()
    {
        $this->controller->simpleRedirect();
        $this->setExpectedException('Stato_DoubleRespond');
        $this->controller->simpleRedirect();
    }
    
    public function testRenderSpecificFile()
    {
        $this->assertEquals('hello world', $this->controller->renderSpecificFile());
    }
        
    public function testRenderSpecificFileWithAssigns()
    {
        $this->assertEquals('hello raphael', $this->controller->renderSpecificFileWithAssigns());
    }
    
    public function testRenderSpecificFileWithLayout()
    {
        $this->assertEquals('<html><body>hello world</body></html>', $this->controller->renderSpecificFileWithLayout());
    }
    
    public function testRenderSpecificFileWithAssignsAndLayout()
    {
        $this->assertEquals('<html><body>hello raphael</body></html>', $this->controller->renderSpecificFileWithAssignsAndLayout());
    }
    
    public function testRenderMissingFile()
    {    
        $this->setExpectedException('Stato_MissingTemplate');
        $this->controller->renderMissingFile();
    }
    
    public function testRenderSpecificTemplate()
    {
        $this->assertEquals('hello world', $this->controller->renderSpecificTemplate());
    }
    
    public function testRenderSpecificTemplateWithLayout()
    {
        $this->assertEquals('<html><body>hello world</body></html>', $this->controller->renderSpecificTemplateWithLayout());
    }
    
    public function testRenderMissingTemplate()
    {
        $this->setExpectedException('Stato_MissingTemplate');
        $this->controller->renderMissingTemplate();
    }
    
    public function testRenderAction()
    {
        $this->assertEquals('hello world baz', $this->controller->renderAction());
    }
    
    public function testPartialCollection()
    {
        $this->assertEquals(
            '<li>foo 1</li><li>bar 2</li><li>baz 3</li>',
            $this->controller->partialTemplateCollection()
        );
    }
    
    public function testPartialCollectionWithSpacer()
    {
        $this->assertEquals(
            '<li>foo 1</li><br /><li>bar 2</li><br /><li>baz 3</li>',
            $this->controller->partialTemplateCollectionWithSpacer()
        );
    }
    
    public function testPartialCollectionWithSpacerTemplate()
    {
        $this->assertEquals(
            '<li>foo 1</li><br /><li>bar 2</li><br /><li>baz 3</li>',
            $this->controller->partialTemplateCollectionWithSpacerTemplate()
        );
    }
    
    public function testRun()
    {
        $this->request->setParams(array('action' => 'index'));
        $this->controller->run();
        $this->assertEquals('hello world', $this->response->getBody());
    }
    
    public function testRunUnexistentAction()
    {
        $this->setExpectedException('Stato_ActionNotFound');
        $this->request->setParams(array('action' => 'dummy'));
        $this->controller->run();
    }
    
    public function testRunWithRender()
    {
        $this->request->setParams(array('action' => 'foo'));
        $this->controller->run();
        $this->assertEquals('hello world', $this->response->getBody());
        $this->assertEquals(200, $this->response->getStatus());
    }
    
    public function testRunWithRenderAndLayout()
    {
        $this->request->setParams(array('action' => 'bar'));
        $this->controller->run();
        $this->assertEquals('<html><body>hello world</body></html>', $this->response->getBody());
        $this->assertEquals(200, $this->response->getStatus());
    }
    
    public function testRunWithRenderAndStatus()
    {
        $this->request->setParams(array('action' => 'baz'));
        $this->controller->run();
        $this->assertEquals('hello world', $this->response->getBody());
        $this->assertEquals(204, $this->response->getStatus());
    }
    
    public function testRenderWithoutArguments()
    {
        $this->request->setParams(array('action' => 'bat'));
        $this->assertEquals('hello world bat', $this->controller->renderWithoutArguments());
    }
}
