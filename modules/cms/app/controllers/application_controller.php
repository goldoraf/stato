<?php

class ApplicationController extends SActionController
{
    public function __construct()
    {
        parent::__construct();
        $this->before_filters->append('load_settings');
    }
    
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
