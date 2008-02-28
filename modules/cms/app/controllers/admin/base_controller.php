<?php

class AdminBaseController extends ApplicationController
{
    protected $layout = 'admin';
    protected $helpers = array('/cms', 'files', 'pages');
    
    public function __construct()
    {
        parent::__construct();
        $this->before_filters->append('load_settings');
        $this->before_filters->append('authenticate');
    }
    
    protected function authenticate()
    {
        if (!isset($this->session['user']))
        {
            $this->session['return_to'] = $this->request->relative_url_root().$this->request->request_uri();
            $this->redirect_to(login_url());
            return;
        }
        
        $this->session['user']->update_attribute('last_access', SDateTime::today());
    }
}

?>
