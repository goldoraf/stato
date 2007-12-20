<?php

class UsersController extends AdminBaseController
{
    public function index()
    {
        $this->users = User::$objects->all();
    }
    
    public function view()
    {
        $this->user = User::$objects->get($this->params['id']);
    }
    
    public function create()
    {
        if (!$this->request->is_post())
        {
            $this->user = new User();
        }
        else
        {
            $this->user = new User($this->params['user']);
            if ($this->user->save())
            {
                $this->flash['notice'] = 'Utilisateur créé !';
                $this->redirect_to(array('action' => 'index'));
                return;
            }
        }
    }
    
    public function update()
    {
        if (!$this->request->is_post())
        {
            $this->user = User::$objects->get($this->params['id']);
        }
        else
        {
            $this->user = User::$objects->get($this->params['user']['id']);
            if ($this->user->update_attributes($this->params['user']))
            {
                $this->flash['notice'] = 'Utilisateur mis à jour !';
                $this->redirect_to(array('action' => 'index'));
                return;
            }
        }
    }
    
    public function delete()
    {
        User::$objects->get($this->params['id'])->delete();
        $this->flash['notice'] = 'Utilisateur supprimé !';
        $this->redirect_to(array('action' => 'index'));
    }
}

?>
