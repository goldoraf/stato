<?php



class BasicController extends Stato_Webflow_Controller
{
    public function initialize()
    {
        $this->beforeFilters->append('ensureLogin');
        $this->afterFilters->append('cleanUp');
    }
    
    public function show()
    {
        $this->respond('ran action');
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

class ConditionalFilterController extends Stato_Webflow_Controller
{
    public function show()
    {
        $this->respond('ran action');
    }
    
    public function anotherAction()
    {
        $this->respond('ran action');
    }
    
    public function showWithoutFilter()
    {
        $this->respond('ran action without filter');
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
    public function initialize()
    {
        $this->beforeFilters->append('ensureLogin', array('except' => 'showWithoutFilter'));
    }
}

class ExceptConditionArrayController extends ConditionalFilterController
{
    public function initialize()
    {
        $this->beforeFilters->append('ensureLogin', array('except' => array('showWithoutFilter', 'anotherAction')));
    }
}

class OnlyConditionController extends ConditionalFilterController
{
    public function initialize()
    {
        $this->beforeFilters->append('ensureLogin', array('only' => 'show'));
    }
}

class OnlyConditionArrayController extends ConditionalFilterController
{
    public function initialize()
    {
        $this->beforeFilters->append('ensureLogin', array('only' => array('anotherAction', 'show')));
    }
}

class BeforeAndAfterConditionController extends ConditionalFilterController
{
    public function initialize()
    {
        $this->beforeFilters->append('ensureLogin', array('only' => 'show'));
        $this->afterFilters->append('cleanUp', array('only' => 'show'));
    }
}

class BasicSkippingController extends BasicController
{
    public function initialize()
    {
        parent::initialize();
        $this->beforeFilters->skip('ensureLogin');
        $this->afterFilters->skip('cleanUp');
    }
    
    public function home()
    {
        $this->respond('ran action');
    }
}

class SkippingWithExceptController extends ConditionalFilterController
{
    public function initialize()
    {
        $this->beforeFilters->append('ensureLogin');
        $this->beforeFilters->skip('ensureLogin', array('except' => 'anotherAction'));
    }
}

class SkippingWithOnlyController extends ConditionalFilterController
{
    public function initialize()
    {
        $this->beforeFilters->append('ensureLogin');
        $this->beforeFilters->skip('ensureLogin', array('only' => 'show'));
    }
}

class TestAroundFilter
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

class AroundController extends Stato_Webflow_Controller
{
    public function initialize()
    {
        $this->aroundFilters->append(new TestAroundFilter());
    }
    
    public function show()
    {
        $this->respond('ran action');
    }
}