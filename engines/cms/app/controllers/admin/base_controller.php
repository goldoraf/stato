<?php

class AdminBaseController extends ApplicationController
{
    protected $layout = 'admin';
    protected $helpers = array('/cms', 'tiny_mce', 'files', 'pages');
    protected $before_filters = array('load_settings', 'authenticate');
    
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
