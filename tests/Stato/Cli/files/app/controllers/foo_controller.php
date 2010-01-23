<?php



class FooController extends Stato_Webflow_Controller
{
    public function index()
    {
        $this->header = __('My app');
        $this->action = __('Say hello');
    }
}