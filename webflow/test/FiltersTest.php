<?php

require_once dirname(__FILE__) . '/../../test/TestsHelper.php';

class TestController extends SActionController
{
    protected function log_processing() {}
    protected function log_benchmarking() {}
    protected function rescue_action($exception) { throw $exception; }
}

class BasicController extends TestController
{
    public function initialize()
    {
        $this->before_filters->append('ensure_login');
        $this->after_filters->append('clean_up');
    }
    
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

class ConditionalFilterController extends TestController
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
    public function initialize()
    {
        $this->before_filters->append('ensure_login', array('except' => 'show_without_filter'));
    }
}

class ExceptConditionArrayController extends ConditionalFilterController
{
    public function initialize()
    {
        $this->before_filters->append('ensure_login', array('except' => array('another_action', 'show_without_filter')));
    }
}

class OnlyConditionController extends ConditionalFilterController
{
    public function initialize()
    {
        $this->before_filters->append('ensure_login', array('only' => 'show'));
    }
}

class OnlyConditionArrayController extends ConditionalFilterController
{
    public function initialize()
    {
        $this->before_filters->append('ensure_login', array('only' => array('another_action', 'show')));
    }
}

class BeforeAndAfterConditionController extends ConditionalFilterController
{
    public function initialize()
    {
        $this->before_filters->append('ensure_login', array('only' => 'show'));
        $this->after_filters->append('clean_up', array('only' => 'show'));
    }
}

class BasicSkippingController extends BasicController
{
    public function initialize()
    {
        parent::initialize();
        $this->before_filters->skip('ensure_login');
        $this->after_filters->skip('clean_up');
    }
    
    public function home_page()
    {
        $this->render_text('ran action');
    }
}

class SkippingWithExceptController extends ConditionalFilterController
{
    public function initialize()
    {
        $this->before_filters->append('ensure_login');
        $this->before_filters->skip('ensure_login', array('except' => 'another_action'));
    }
}

class SkippingWithOnlyController extends ConditionalFilterController
{
    public function initialize()
    {
        $this->before_filters->append('ensure_login');
        $this->before_filters->skip('ensure_login', array('only' => 'show'));
    }
}

class TestAroundFilter implements SAroundFilter
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

class AroundController extends TestController
{
    public function initialize()
    {
        $this->around_filters->append(new TestAroundFilter());
    }
    
    public function show()
    {
        $this->render_text('ran action');
    }
}

class FiltersTest extends StatoTestCase
{
    protected function process($controller, $action = 'show', $params = array())
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new SRequest();
        $params['action'] = $action;
        $request->inject_params($params);
        $c = new $controller();
        return $c->dispatch($request, new SResponse());
    }
    
    public function test_basic()
    {
        $this->assertEquals('ran action', $this->process('BasicController')->body);
        $this->assertEquals('ensure_login', $this->process('BasicController')->assigns['ran_before_filter']);
        $this->assertEquals('clean_up', $this->process('BasicController')->assigns['ran_after_filter']);
    }
    
    public function test_except_condition()
    {
        $this->assertEquals('ensure_login', $this->process('ExceptConditionController')->assigns['ran_before_filter']);
        $this->assertEquals('ensure_login', $this->process('ExceptConditionController', 'another_action')->assigns['ran_before_filter']);
        $this->assertTrue(!isset($this->process('ExceptConditionController', 'show_without_filter')->assigns['ran_before_filter']));
    }
    
    public function test_except_condition_array()
    {
        $this->assertEquals('ensure_login', $this->process('ExceptConditionArrayController')->assigns['ran_before_filter']);
        $this->assertTrue(!isset($this->process('ExceptConditionArrayController', 'another_action')->assigns['ran_before_filter']));
        $this->assertTrue(!isset($this->process('ExceptConditionArrayController', 'show_without_filter')->assigns['ran_before_filter']));
    }
    
    public function test_only_condition()
    {
        $this->assertEquals('ensure_login', $this->process('OnlyConditionController')->assigns['ran_before_filter']);
        $this->assertTrue(!isset($this->process('OnlyConditionController', 'another_action')->assigns['ran_before_filter']));
        $this->assertTrue(!isset($this->process('OnlyConditionController', 'show_without_filter')->assigns['ran_before_filter']));
    }
    
    public function test_only_condition_array()
    {
        $this->assertEquals('ensure_login', $this->process('OnlyConditionArrayController')->assigns['ran_before_filter']);
        $this->assertEquals('ensure_login', $this->process('OnlyConditionArrayController', 'another_action')->assigns['ran_before_filter']);
        $this->assertTrue(!isset($this->process('OnlyConditionArrayController', 'show_without_filter')->assigns['ran_before_filter']));
    }
    
    public function test_before_and_after_condition()
    {
        $this->assertEquals('ensure_login', $this->process('BeforeAndAfterConditionController')->assigns['ran_before_filter']);
        $this->assertEquals('clean_up', $this->process('BeforeAndAfterConditionController')->assigns['ran_after_filter']);
        $this->assertTrue(!isset($this->process('BeforeAndAfterConditionController', 'another_action')->assigns['ran_before_filter']));
        $this->assertTrue(!isset($this->process('BeforeAndAfterConditionController', 'show_without_filter')->assigns['ran_after_filter']));
    }
    
    public function test_basic_skipping()
    {
        $this->assertTrue(!isset($this->process('BasicSkippingController', 'home_page')->assigns['ran_before_filter']));
        $this->assertTrue(!isset($this->process('BasicSkippingController', 'home_page')->assigns['ran_after_filter']));
    }
    
    public function test_skipping_with_except()
    {
        $this->assertEquals('ensure_login', $this->process('SkippingWithExceptController', 'another_action')->assigns['ran_before_filter']);
        $this->assertTrue(!isset($this->process('SkippingWithExceptController', 'show')->assigns['ran_before_filter']));
    }
    
    public function test_skipping_with_only()
    {
        $this->assertEquals('ensure_login', $this->process('SkippingWithOnlyController', 'another_action')->assigns['ran_before_filter']);
        $this->assertTrue(!isset($this->process('SkippingWithOnlyController', 'show')->assigns['ran_before_filter']));
    }
    
    public function test_around()
    {
        $this->assertTrue($this->process('AroundController')->assigns['ran_before']);
        $this->assertTrue($this->process('AroundController')->assigns['ran_after']);
    }
}

