<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'controller.php';
require_once 'request.php';
require_once 'response.php';
require_once 'filters.php';
require_once 'helpers/string.php';

require_once 'files/controllers_with_filters.php';

class Stato_FiltersTest extends PHPUnit_Framework_TestCase
{
    public function process($controller, $action = 'show', $params = array())
    {
        $this->request = new Stato_Request();
        $this->response = new Stato_Response();
        $this->request->setParams(array_merge($params, array('action' => $action)));
        $this->controller = new $controller($this->request, $this->response);
        $this->controller->run();
    }
    
    public function testBasics()
    {
        $this->process('BasicController');
        $this->assertEquals('ran action', $this->response->getBody());
        $this->assertEquals('ensureLogin', $this->controller->ranBeforeFilter);
        $this->assertEquals('cleanUp', $this->controller->ranAfterFilter);
    }
    
    public function testExceptCondition()
    {
        $this->process('ExceptConditionController');
        $this->assertEquals('ran action', $this->response->getBody());
        $this->assertEquals('ensureLogin', $this->controller->ranBeforeFilter);
        $this->process('ExceptConditionController', 'showWithoutFilter');
        $this->assertEquals('ran action without filter', $this->response->getBody());
        $this->assertFalse(isset($this->controller->ranBeforeFilter));
    }
    
    public function testExceptConditionArray()
    {
        $this->process('ExceptConditionArrayController');
        $this->assertEquals('ran action', $this->response->getBody());
        $this->assertEquals('ensureLogin', $this->controller->ranBeforeFilter);
        $this->process('ExceptConditionArrayController', 'anotherAction');
        $this->assertEquals('ran action', $this->response->getBody());
        $this->assertFalse(isset($this->controller->ranBeforeFilter));
        $this->process('ExceptConditionArrayController', 'showWithoutFilter');
        $this->assertEquals('ran action without filter', $this->response->getBody());
        $this->assertFalse(isset($this->controller->ranBeforeFilter));
    }
    
    public function testOnlyCondition()
    {
        $this->process('OnlyConditionController');
        $this->assertEquals('ran action', $this->response->getBody());
        $this->assertEquals('ensureLogin', $this->controller->ranBeforeFilter);
        $this->process('OnlyConditionController', 'anotherAction');
        $this->assertEquals('ran action', $this->response->getBody());
        $this->assertFalse(isset($this->controller->ranBeforeFilter));
        $this->process('OnlyConditionController', 'showWithoutFilter');
        $this->assertEquals('ran action without filter', $this->response->getBody());
        $this->assertFalse(isset($this->controller->ranBeforeFilter));
    }
    
    public function testOnlyConditionArray()
    {
        $this->process('OnlyConditionArrayController');
        $this->assertEquals('ran action', $this->response->getBody());
        $this->assertEquals('ensureLogin', $this->controller->ranBeforeFilter);
        $this->process('OnlyConditionArrayController', 'anotherAction');
        $this->assertEquals('ran action', $this->response->getBody());
        $this->assertTrue(isset($this->controller->ranBeforeFilter));
        $this->process('OnlyConditionArrayController', 'showWithoutFilter');
        $this->assertEquals('ran action without filter', $this->response->getBody());
        $this->assertFalse(isset($this->controller->ranBeforeFilter));
    }
    
    public function testBeforeAndAfterCondition()
    {
        $this->process('BeforeAndAfterConditionController');
        $this->assertEquals('ran action', $this->response->getBody());
        $this->assertEquals('ensureLogin', $this->controller->ranBeforeFilter);
        $this->assertEquals('cleanUp', $this->controller->ranAfterFilter);
        $this->process('BeforeAndAfterConditionController', 'anotherAction');
        $this->assertFalse(isset($this->controller->ranBeforeFilter));
        $this->assertFalse(isset($this->controller->ranAfterFilter));
        $this->process('BeforeAndAfterConditionController', 'showWithoutFilter');
        $this->assertFalse(isset($this->controller->ranBeforeFilter));
        $this->assertFalse(isset($this->controller->ranAfterFilter));
    }
    
    public function testBasicSkipping()
    {
        $this->process('BasicSkippingController', 'home');
        $this->assertFalse(isset($this->controller->ranBeforeFilter));
        $this->assertFalse(isset($this->controller->ranAfterFilter));
    }
    
    public function testSkippingWithExcept()
    {
        $this->process('SkippingWithExceptController', 'anotherAction');
        $this->assertEquals('ran action', $this->response->getBody());
        $this->assertEquals('ensureLogin', $this->controller->ranBeforeFilter);
        $this->process('SkippingWithExceptController', 'show');
        $this->assertEquals('ran action', $this->response->getBody());
        $this->assertFalse(isset($this->controller->ranBeforeFilter));
    }
    
    public function testSkippingWithOnly()
    {
        $this->process('SkippingWithExceptController', 'anotherAction');
        $this->assertEquals('ran action', $this->response->getBody());
        $this->assertEquals('ensureLogin', $this->controller->ranBeforeFilter);
        $this->process('SkippingWithExceptController', 'show');
        $this->assertEquals('ran action', $this->response->getBody());
        $this->assertFalse(isset($this->controller->ranBeforeFilter));
    }
    
    public function testAround()
    {
        $this->process('AroundController');
        $this->assertTrue($this->controller->ranBefore);
        $this->assertTrue($this->controller->ranAfter);
    }
}
