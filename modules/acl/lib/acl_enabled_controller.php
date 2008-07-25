<?php

class AclEnabledController extends SActionController
{
    public function __construct()
    {
        parent::__construct();
        
        if (AclEngine::config('use_permission_system'))
            $this->before_filters->append('authorize_action');
        else
            $this->before_filters->append('login_required');
    }
    
    // overwrite this method in your controller if you only want to protect certain actions of the controller
    protected function is_protected($action)
    {
        return true;
    }
    
    protected function is_authorized($user)
    {
        return true;
    }
    
    protected function login_required()
    {
        $this->current_user = (isset($this->session['user'])) ? $this->session['user'] : false;
        
        if (!$this->is_protected($this->action_name()))
           return true;
        
        if ($this->current_user && $this->is_authorized($this->current_user))
            return true;
        
        $this->store_location();
        $this->access_denied();
        return false;
    }
    
    protected function authorize_action()
    {
        $this->current_user = (isset($this->session['user'])) ? $this->session['user'] : false;
        
        $controller = $this->request->params['controller'];
        $action = $this->action_name();
        $required_permission = "$controller/$action";
        
        // EVERYONE should be able to get to the root.
        if (!isset($this->request->params['controller']) && isset($this->request->params['action']))
            return true;
        
        if ($this->current_user)
        {
            if (!AclEngine::is_authorized($this->current_user, $controller, $action))
            {
                $this->flash['warning'] = __("Permission warning: You are not authorized for the action '%s'.", array($required_permission));
                $this->redirect_back();
                return false;
            }
        }
        else
        {
            if (!AclEngine::is_guest_user_authorized($controller, $action))
            {
                $this->flash['warning'] = __("You need to log in.");
                $this->store_location();
                $this->access_denied();
                return false;
            }
        }
        
        $this->session['prev_uri'] = $this->request->request_uri();
        return true;
    }
    
    protected function store_location()
    {
        $this->session['return_to']
            = $this->request->relative_url_root().$this->request->request_uri();
    }
    
    protected function access_denied()
    {
        $this->redirect_to(AclEngine::config('login_page'));
    }
    
    protected function redirect_to_stored_or_default($default)
    {
        if (($path = $this->session['return_to']) !== null)
        {
            unset($this->session['return_to']);
            $this->redirect_to($path);
            return;
        }
        elseif (isset($this->params['return_to']))
        {
            $this->redirect_to($this->params['return_to']);
            return;
        }
        $this->redirect_to($default);
    }
}

?>
