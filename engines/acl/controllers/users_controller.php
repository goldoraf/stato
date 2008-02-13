<?php

class UsersController extends ApplicationController
{
    public function index()
    {
        list($this->users_pages, $this->users) = $this->paginate(User::$objects->all(), 20);
    }
    
    public function show()
    {
        $this->user = User::$objects->get($this->params['id']);
    }
    
    public function create()
    {
        if (!$this->request->is_post())
        {
            $this->user = new User();
            $this->render();
            return;
        }
        
        $this->user = new User($this->params['user']);
        $this->user->new_password = true;
        $this->user->verified = true;
        
        try {
            User::begin_transaction($this->user);
            if ($this->user->save())
            {
                $this->flash['notice'] = __('User creation successful.');  
                User::commit();  
                $this->redirect_to(array('action' => 'index'));
                return;
            }
        } catch (PDOException $e) {
            $this->user = User::rollback();
            $this->flash['warning'] = __('Error creating account.');
        }
    }
    
    public function edit()
    {
        $this->user = User::$objects->get($this->params['id']);
        
        if ($this->request->is_post())
        {
            foreach ($this->params['user'] as $k => $v)
                if (in_array($k, AclEngine::config('changeable_fields'))) $this->user->$k = $v;
            
            if ($this->user->save())
            {
                $this->flash['notice'] = __("User details updated.");
                $this->redirect_to(array('action' => 'edit', 'id' => $this->user->id));
                return;
            }
        }
        
        $this->all_roles = Role::$objects->filter('name != ?', array(AclEngine::config('guest_role_name')));
    }
    
    public function edit_roles()
    {
        $this->user = User::$objects->get($this->params['id']);
        try {
            User::begin_transaction($this->user);
            $this->user->roles->singular_ids($this->params['user']['roles']);
            User::commit();
            $this->flash['notice'] = __("Roles updated for user '%s'.", array($this->user->login));
        } catch (PDOException $e) {
            $this->user = User::rollback();
            $this->flash['warning'] = __('Roles could not be edited at this time. Please retry.');
        }
        $this->redirect_back();
    }
    
    public function change_password()
    {
        $this->user = User::$objects->get($this->params['id']);
        try {
            User::begin_transaction($this->user);
            $this->user->change_password($this->params['user']['password'],
                                         $this->params['user']['password_confirmation']);
            if ($this->user->save())
            {
                $notifier = new UserNotifier();
                $notifier->deliver_change_password($this->user, $this->params['user']['password']);
                User::commit();
                $this->flash['notice'] = __('Updated password emailed to %s.', array($this->user->email));
                $this->redirect_back();
                return;
            }
        } catch (Exception $e) {
            $this->user = User::rollback();
            $this->flash['warning'] = __('Error updating password.');
        }
    }
    
    public function delete()
    {
        $this->user = User::$objects->get($this->params['id']);
        if (AclEngine::config('delayed_delete'))
        {
           try {
                User::begin_transaction($this->user);
                $key = $this->user->set_delete_after();
                $url = $this->url_for(array('action' => 'restore_deleted', 'user_id' => $this->user->id, 'key' => $key));
                $notifier = new UserNotifier();
                $notifier->deliver_pending_delete($this->user, $url);
                User::commit();
                $this->flash['notice'] = __("The account of '%s' has been scheduled for deletion. It will be removed in %s days.", array($this->user->login, AclEngine::config('delayed_delete_days')));
                $this->redirect_back();
                return;
            } catch (Exception $e) {
                $this->user = User::rollback();
                $this->flash['warning'] = __('The delete instructions were not sent. Please try again later.');
                $this->redirect_back();
                return;
            }
        }
        else
        {
            $notifier = new UserNotifier();
            $notifier->deliver_delete($this->user);
            $this->flash['notice'] = __('The account for %s was successfully deleted.', array($this->user->login));
            $this->user->delete();
            $this->redirect_back();
        }
    }
}

?>
