<?php

use Stato\Webflow\Controller;

class FooController extends Controller
{
    public function index()
    {
        $this->header = __('My app');
        $this->action = __('Say hello');
    }
}