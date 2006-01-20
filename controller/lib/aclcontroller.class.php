<?php

/**
 * ACLController
 * 
 * @package 
 * @author goldoraf
 * @copyright Copyright (c) 2005
 * @version 0.1
 * @access public
 **/
class ACLController extends ActionController
{	
	function & ACLController()
	{
        parent::ActionController();
        $this->beforeFilter = 'authorize';
        $this->user =& new UserModel();
    }
    
    function authorize()
    {
        $requestPerm = $this->request->module.'/'.$this->request->controller.'/'.$this->request->action;
        $user = $this->session->get('user');
        if (!$user)
        {
            $this->flashMsg("Login obligatoire !");
            $this->redirect('index', 'login');
            return false;
        }
        
    }
    
    

}

?>
