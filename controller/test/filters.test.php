<?php

require_once(CORE_DIR.'/controller/controller.php');
require_once('controller_mocks.php');

class BasicController extends SActionController
{
    public $beforeFilters = array('ensureLogin');
    public $afterFilters  = array('cleanUp');
    
    public function show()
    {
        $this->renderText('ran action');
    }
    
    protected function ensureLogin()
    {
        $this->ranBeforeFilter = 'ensureLogin';
    }
    
    protected function cleanUp()
    {
        $this->ranAfterFilter = 'cleanUp';
    }
}

class ConditionalFilterController extends SActionController
{
    public function show()
    {
        $this->renderText('ran action');
    }
    
    public function another_action()
    {
        $this->renderText('ran action');
    }
    
    public function show_without_filter()
    {
        $this->renderText('ran action without filter');
    }
    
    protected function ensureLogin()
    {
        $this->ranBeforeFilter = 'ensureLogin';
    }
    
    protected function cleanUp()
    {
        $this->ranAfterFilter = 'cleanUp';
    }
}

class ExceptConditionController extends ConditionalFilterController
{
    public $beforeFilters = array(array('ensureLogin', 'except' => 'show_without_filter'));
}

class ExceptConditionArrayController extends ConditionalFilterController
{
    public $beforeFilters = array(array('ensureLogin', 'except' => array('another_action', 'show_without_filter')));
}

class OnlyConditionController extends ConditionalFilterController
{
    public $beforeFilters = array(array('ensureLogin', 'only' => 'show'));
}

class OnlyConditionArrayController extends ConditionalFilterController
{
    public $beforeFilters = array(array('ensureLogin', 'only' => array('another_action', 'show')));
}

class BeforeAndAfterConditionController extends ConditionalFilterController
{
    public $beforeFilters = array(array('ensureLogin', 'only' => 'show'));
    public $afterFilters = array(array('cleanUp', 'only' => 'show'));
}

class SkippingController extends BasicController
{
    public $skipBeforeFilters = array('ensureLogin');
    public $skipAfterFilters = array('cleanUp');
    
    public function home_page()
    {
        $this->renderText('ran action');
    }
}

class AroundFilter
{
    public function before($controller)
    {
        $controller->ranBefore = true;
    }
    
    public function after($controller)
    {
        $controller->ranAfter = true;
    }
}

class AroundController extends SActionController
{
    public function __construct()
    {
        parent::__construct();
        $this->aroundFilters[] = new AroundFilter();
    }
    
    public function show()
    {
        $this->renderText('ran action');
    }
}

class FiltersTest extends UnitTestCase
{
    public function testBasic()
    {
        $this->assertEqual('ran action', $this->process('BasicController')->body);
        $this->assertEqual('ensureLogin', $this->process('BasicController')->assigns['ranBeforeFilter']);
        $this->assertEqual('cleanUp', $this->process('BasicController')->assigns['ranAfterFilter']);
    }
    
    public function testExceptCondition()
    {
        $this->assertEqual('ensureLogin', $this->process('ExceptConditionController')->assigns['ranBeforeFilter']);
        $this->assertEqual('ensureLogin', $this->process('ExceptConditionController', 'another_action')->assigns['ranBeforeFilter']);
        $this->assertEqual(Null, $this->process('ExceptConditionController', 'show_without_filter')->assigns['ranBeforeFilter']);
    }
    
    public function testExceptConditionArray()
    {
        $this->assertEqual('ensureLogin', $this->process('ExceptConditionArrayController')->assigns['ranBeforeFilter']);
        $this->assertEqual(Null, $this->process('ExceptConditionArrayController', 'another_action')->assigns['ranBeforeFilter']);
        $this->assertEqual(Null, $this->process('ExceptConditionArrayController', 'show_without_filter')->assigns['ranBeforeFilter']);
    }
    
    public function testOnlyCondition()
    {
        $this->assertEqual('ensureLogin', $this->process('OnlyConditionController')->assigns['ranBeforeFilter']);
        $this->assertEqual(Null, $this->process('OnlyConditionController', 'another_action')->assigns['ranBeforeFilter']);
        $this->assertEqual(Null, $this->process('OnlyConditionController', 'show_without_filter')->assigns['ranBeforeFilter']);
    }
    
    public function testOnlyConditionArray()
    {
        $this->assertEqual('ensureLogin', $this->process('OnlyConditionArrayController')->assigns['ranBeforeFilter']);
        $this->assertEqual('ensureLogin', $this->process('OnlyConditionArrayController', 'another_action')->assigns['ranBeforeFilter']);
        $this->assertEqual(Null, $this->process('OnlyConditionArrayController', 'show_without_filter')->assigns['ranBeforeFilter']);
    }
    
    public function testBeforeAndAfterCondition()
    {
        $this->assertEqual('ensureLogin', $this->process('BeforeAndAfterConditionController')->assigns['ranBeforeFilter']);
        $this->assertEqual('cleanUp', $this->process('BeforeAndAfterConditionController')->assigns['ranAfterFilter']);
        $this->assertEqual(Null, $this->process('BeforeAndAfterConditionController', 'another_action')->assigns['ranBeforeFilter']);
        $this->assertEqual(Null, $this->process('BeforeAndAfterConditionController', 'show_without_filter')->assigns['ranAfterFilter']);
    }
    
    public function testSkipping()
    {
        $this->assertEqual(Null, $this->process('SkippingController', 'home_page')->assigns['ranBeforeFilter']);
        $this->assertEqual(Null, $this->process('SkippingController', 'home_page')->assigns['ranAfterFilter']);
    }
    
    public function testAround()
    {
        $this->assertTrue($this->process('AroundController')->assigns['ranBefore']);
        $this->assertTrue($this->process('AroundController')->assigns['ranAfter']);
    }
    
    private function process($controller, $action = 'show')
    {
        $request = new MockRequest();
        $request->action = $action;
        $c = new $controller();
        return $c->process($request, new MockResponse());
    }
}

?>
