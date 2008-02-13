<?php

class AuthController extends ApplicationController
{
    protected $protected_actions = array('login', 'signup', 'forgot_password');
    
    public function login()
    {
        if (!$this->request->is_post())
        {
            $this->render();
            return;
        }
        $user = AclEngine::authenticate($this->params['login'], $this->params['password']);
        if ($user)
        {
            $this->session['user'] = $user;
            $this->session['user']->logged_in_on = SDateTime::now();
            $this->session['user']->save();
            $this->flash['notice'] = __('Login successful');
            $this->redirect_to_stored_or_default(array('action' => 'home'));
            return;
        }
        else
        {
            $this->flash['warning'] = __('Login unsuccessful');
            $this->login = $this->params['login'];
        }
    }
    
    public function logout()
    {
        unset($this->session['user']);
        $this->redirect_to(array('action' => 'login'));
    }
    
    public function signup()
    {
        if (!$this->request->is_post())
        {
            $this->user = new User();
            $this->render();
            return;
        }
        
        unset($this->params['user']['verified']);
        $this->user = new User($this->params['user']);
        $this->user->new_password = true;
        if (AclEngine::config('confirm_account') === false) $this->user->verified = true;
        
        try {
            User::begin_transaction($this->user);
            
            if (AclEngine::config('use_permission_system'))
                $this->user->roles->add(Role::$objects->get("name = '".AclEngine::config('user_role_name')."'"));
            
            if ($this->user->save())
            {
                $this->flash['notice'] = __('Signup successful !');
                if (AclEngine::config('confirm_account') === true)
                {
                    $key = $this->user->generate_security_token();
                    $url = $this->url_for(array('action' => 'confirm', 'user_id' => $this->user->id, 'key' => $key));
                    $notifier = new UserNotifier();
                    $notifier->deliver_signup($this->user, $this->params['user']['password'], $url);
                    $this->flash['notice'].= __(' Please check your registered email account to verify your account registration and continue with the login.');
                }
                else
                    $this->flash['notice'].= __(' Please log in.');
                    
                User::commit();
                    
                $this->redirect_to(array('action' => 'login'));
                return;
            }
        } catch (PDOException $e) {
            $this->user = User::rollback();
            $this->flash['warning'] = __('Error creating account. Please retry.');
        } catch (Exception $e) {
            $this->flash['notice'] = __('Error creating account: confirmation email not sent');
            $this->logger->error('Unable to send confirmation E-Mail:');
            $this->logger->error($e->__toString());
        }
    }
    
    public function confirm()
    {
        $user = AclEngine::authenticate_by_token($this->params['user_id'], $this->params['key']);
        if ($user)
        {
            $user->set_as_verified();
            $this->flash['notice'] = __('Account verified! Please log in.');
        }
        //else
            //$this->flash['warning'] = '...'; faut-il un message pour demander à l'user de recréer un compte ???
        
        $this->redirect_to(array('action' => 'login'));
    }
    
    public function forgot_password()
    {
        if (isset($this->session['user']))
        {
            $this->flash['notice'] = __('You are currently logged in. You may change your password now.');
            $this->redirect_to(array('action' => 'change_password'));
            return;
        }
        if ($this->request->is_post())
        {
            if (empty($this->params['email']))
                $this->flash['warning'] = __('Please enter a valid email address.');
            elseif (($this->user = User::$objects->filter('email = ?', array($this->params['email']))->first()) === null)
                $this->flash['warning'] = __('We could not find a user with this email address.'); // sécurité ???
            else
            {
                try {
                    User::begin_transaction($this->user);
                    $this->user->change_password(AclEngine::random_password());
                    $this->user->save();
                    $notifier = new UserNotifier();
                    $notifier->deliver_forgot_password($this->user);
                    User::commit();
                    $this->flash['notice'] = __("Instructions on resetting your password have been emailed to %s.", array($this->user->email));
                    $this->redirect_to_stored_or_default(array('action' => 'login'));
                    return;
                } catch (Exception $e) {
                    $this->user = User::rollback();
                    $this->flash['warning'] = __("Your password could not be emailed to %s.", array($this->user->email));
                }
            }
        }
    }
    
    public function edit()
    {
        if ($this->generate_filled_in()) return;
        
        try {
            User::begin_transaction($this->user);
            foreach ($this->params['user'] as $k => $v)
                if (in_array($k, AclEngine::config('changeable_fields'))) $this->user->$k = $v;
            
            if ($this->user->save())
                $this->flash['notice'] = __("User details updated.");
            else
                $this->flash['warning'] = __("Details could not be updated! Please retry.");
                
            User::commit();
        } catch (Exception $e) {
            $this->user = User::rollback();
            $this->flash['warning'] = __("Error updating user details. Please try again later.");
        }
    }
    
    public function change_password()
    {
        if ($this->generate_filled_in()) return;
        
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
                $this->redirect_to_stored_or_default(array('action' => 'change_password'));
                return;
            }
            else
                $this->flash['warning'] = __('There was a problem saving the password. Please retry.');
            
        } catch (Exception $e) {
            $this->user = User::rollback();
            $this->flash['warning'] = __('Password could not be changed at this time. Please retry.');
        }
    }
    
    public function delete()
    {
        $this->user = $this->session['user'];
        if (AclEngine::config('delayed_delete'))
        {
           try {
                User::begin_transaction($this->user);
                $key = $this->user->set_delete_after();
                $url = $this->url_for(array('action' => 'restore_deleted', 'user_id' => $this->user->id, 'key' => $key));
                $notifier = new UserNotifier();
                $notifier->deliver_pending_delete($this->user, $url);
                User::commit();
                $this->flash['notice'] = __('The account has been scheduled for deletion. It will be removed in %s days.', array(AclEngine::config('delayed_delete_days')));
                $this->logout();
                return;
            } catch (Exception $e) {
                $this->user = User::rollback();
                $this->flash['warning'] = __('The delete instructions were not sent. Please try again later.');
                $this->redirect_to_stored_or_default(array('action' => 'home'));
                return;
            }
        }
        else
        {
            $notifier = new UserNotifier();
            $notifier->deliver_delete($this->user);
            $this->flash['notice'] = __('The account for %s was successfully deleted.', array($this->user->login));
            $this->user->delete();
            $this->logout();
        }
    }
    
    public function restore_deleted()
    {
        $user = AclEngine::authenticate_by_token($this->params['user_id'], $this->params['key']);
        if ($user)
        {
            $user->deleted = false;
            $user->save();
            $this->flash['notice'] = __('Account restored! Please log in.');
        }
        //else
            //$this->flash['warning'] = '...'; faut-il un message  ???
        
        $this->redirect_to(array('action' => 'login'));
    }
    
    protected function is_protected($action)
    {    
        return !in_array($action, array('login', 'signup', 'forgot_password', 'confirm', 'restore_deleted'));   
    }
    
    protected function generate_filled_in()
    {
        $this->user = $this->session['user'];
        if (!$this->request->is_post())
        {
            $this->render();
            return true;
        }
        return false;
    }
}

?>
