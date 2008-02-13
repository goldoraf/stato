<?php

class LoginController extends SActionController
{
    public function index()
    {
    
    }
    
    public function authenticate()
    {
        $user = User::authenticate($this->params['login'], $this->params['password']);
        if ($user)
        {
            $this->session['user'] = $user;
            
            if (($path = $this->session['return_to']) !== Null)
            {
                unset($this->session['return_to']);
                $this->redirect_to($path);
            }
            else $this->redirect_to(array('controller' => 'admin/pages'));
        }
        else
        {
            $this->flash['notice'] = 'Votre identifiant et/ou votre mot de passe n\'est pas valide .';
            $this->redirect_to(array('action' => 'index'));
        }
    }
    
    public function logout()
    {
        $this->session->destroy();
        
        $this->flash['notice'] = 'Déconnecté';
        $this->redirect_to(array('action' => 'index'));
    }
}

?>
