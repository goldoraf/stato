<?php

require_once(STATO_CORE_PATH.'/controller/controller.php');
require_once(STATO_CORE_PATH.'/view/view.php');
require_once('controller_mocks.php');

class BasicController extends SActionController
{
    public $before_filters = array('ensure_login');
    public $after_filters  = array('clean_up');
    
    public function show()
    {
        $this->render_text('ran action');
    }
    
    protected function ensure_login()
    {
        $this->ran_before_filter = 'ensure_login';
    }
    
    protected function clean_up()
    {
        $this->ran_after_filter = 'clean_up';
    }
}

class ConditionalFilterController extends SActionController
{
    public function show()
    {
        $this->render_text('ran action');
    }
    
    public function another_action()
    {
        $this->render_text('ran action');
    }
    
    public function show_without_filter()
    {
        $this->render_text('ran action without filter');
    }
    
    protected function ensure_login()
    {
        $this->ran_before_filter = 'ensure_login';
    }
    
    protected function clean_up()
    {
        $this->ran_after_filter = 'clean_up';
    }
}

class ExceptConditionController extends ConditionalFilterController
{
    public $before_filters = array(array('ensure_login', 'except' => 'show_without_filter'));
}

class ExceptConditionArrayController extends ConditionalFilterController
{
    public $before_filters = array(array('ensure_login', 'except' => array('another_action', 'show_without_filter')));
}

class OnlyConditionController extends ConditionalFilterController
{
    public $before_filters = array(array('ensure_login', 'only' => 'show'));
}

class OnlyConditionArrayController extends ConditionalFilterController
{
    public $before_filters = array(array('ensure_login', 'only' => array('another_action', 'show')));
}

class BeforeAndAfterConditionController extends ConditionalFilterController
{
    public $before_filters = array(array('ensure_login', 'only' => 'show'));
    public $after_filters = array(array('clean_up', 'only' => 'show'));
}

class SkippingController extends BasicController
{
    public $skip_before_filters = array('ensure_login');
    public $skip_after_filters = array('clean_up');
    
    public function home_page()
    {
        $this->render_text('ran action');
    }
}

class AroundFilter
{
    public function before($controller)
    {
        $controller->ran_before = true;
    }
    
    public function after($controller)
    {
        $controller->ran_after = true;
    }
}

class AroundController extends SActionController
{
    public function __construct()
    {
        parent::__construct();
        $this->around_filters[] = new AroundFilter();
    }
    
    public function show()
    {
        $this->render_text('ran action');
    }
}

class FiltersTest extends UnitTestCase
{
    public function test_basic()
    {
        $this->assertEqual('ran action', $this->process('BasicController')->body);
        $this->assertEqual('ensure_login', $this->process('BasicController')->assigns['ran_before_filter']);
        $this->assertEqual('clean_up', $this->process('BasicController')->assigns['ran_after_filter']);
    }
    
    public function test_except_condition()
    {
        $this->assertEqual('ensure_login', $this->process('ExceptConditionController')->assigns['ran_before_filter']);
        $this->assertEqual('ensure_login', $this->process('ExceptConditionController', 'another_action')->assigns['ran_before_filter']);
        $this->assertEqual(Null, $this->process('ExceptConditionController', 'show_without_filter')->assigns['ran_before_filter']);
    }
    
    public function test_except_condition_array()
    {
        $this->assertEqual('ensure_login', $this->process('ExceptConditionArrayController')->assigns['ran_before_filter']);
        $this->assertEqual(Null, $this->process('ExceptConditionArrayController', 'another_action')->assigns['ran_before_filter']);
        $this->assertEqual(Null, $this->process('ExceptConditionArrayController', 'show_without_filter')->assigns['ran_before_filter']);
    }
    
    public function test_only_condition()
    {
        $this->assertEqual('ensure_login', $this->process('OnlyConditionController')->assigns['ran_before_filter']);
        $this->assertEqual(Null, $this->process('OnlyConditionController', 'another_action')->assigns['ran_before_filter']);
        $this->assertEqual(Null, $this->process('OnlyConditionController', 'show_without_filter')->assigns['ran_before_filter']);
    }
    
    public function test_only_condition_array()
    {
        $this->assertEqual('ensure_login', $this->process('OnlyConditionArrayController')->assigns['ran_before_filter']);
        $this->assertEqual('ensure_login', $this->process('OnlyConditionArrayController', 'another_action')->assigns['ran_before_filter']);
        $this->assertEqual(Null, $this->process('OnlyConditionArrayController', 'show_without_filter')->assigns['ran_before_filter']);
    }
    
    public function test_before_and_after_condition()
    {
        $this->assertEqual('ensure_login', $this->process('BeforeAndAfterConditionController')->assigns['ran_before_filter']);
        $this->assertEqual('clean_up', $this->process('BeforeAndAfterConditionController')->assigns['ran_after_filter']);
        $this->assertEqual(Null, $this->process('BeforeAndAfterConditionController', 'another_action')->assigns['ran_before_filter']);
        $this->assertEqual(Null, $this->process('BeforeAndAfterConditionController', 'show_without_filter')->assigns['ran_after_filter']);
    }
    
    public function test_skipping()
    {
        $this->assertEqual(Null, $this->process('SkippingController', 'home_page')->assigns['ran_before_filter']);
        $this->assertEqual(Null, $this->process('SkippingController', 'home_page')->assigns['ran_after_filter']);
    }
    
    public function test_around()
    {
        $this->assertTrue($this->process('AroundController')->assigns['ran_before']);
        $this->assertTrue($this->process('AroundController')->assigns['ran_after']);
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
