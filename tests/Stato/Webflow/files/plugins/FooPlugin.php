<?php

use Stato\Webflow\Plugin;

class FooPlugin extends Plugin 
{
    public function preRouting()
    {
        $p =  $this->request->params;
        $this->request->params['preRouting'] = 'Foo';
    }

    public function postRouting()
    {
        $this->request->params['postRouting'] = 'Foo';
    }

    public function preDispatch()
    {
        if(is_array($this->request->params['preDispatch'])) {
            $params = $this->request->params['preDispatch'];
            $params[] = 'Foo';
            $this->request->params['preDispatch'] = $params;
        } else {
            $this->request->params['preDispatch'] = array('Foo');
        }
    }

}
