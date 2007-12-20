<?php

class ApplicationController extends SActionController
{
    protected $before_filters = array('load_settings');
    
    protected function load_settings()
    {
        Configuration::initialize();
    }
    
    protected function render_json($data)
    {
        return $this->render_text(json_encode($data));
    }
}

?>
