<?php

class UserNotifier extends SMailer
{
    public function signup($user, $password, $url)
    {
        $this->setup_email($user);
        $this->subject.= 'Welcome to '.AclEngine::config('app_name').'!';
        $this->body = array
        (
            'app_name' => AclEngine::config('app_name'),
            'name'     => $user->__repr(),
            'login'    => $user->login,
            'password' => $password,
            'url'      => $url
        );
    }
    
    public function change_password($user, $password)
    {
        $this->setup_email($user);
        $this->subject.= 'Changed password notification';
        $this->body = array
        (
            'app_name' => AclEngine::config('app_name'),
            'name'     => $user->__repr(),
            'login'    => $user->login,
            'password' => $password
        );
    }
    
    public function forgot_password($user)
    {
        $this->setup_email($user);
        $this->subject.= 'Forgotten password notification';
        $this->body = array
        (
            'app_name' => AclEngine::config('app_name'),
            'name'     => $user->__repr(),
            'login'    => $user->login,
            'password' => $user->password
        );
    }
    
    public function pending_delete($user, $url)
    {
        $this->setup_email($user);
        $this->subject.= 'Delete user notification';
        $this->body = array
        (
            'app_name' => AclEngine::config('app_name'),
            'name'     => $user->__repr(),
            'days'     => AclEngine::config('delayed_delete_days'),
            'url'      => $url
        );
    }
    
    public function deliver_mail($method_name, $args)
    {
        if (AclEngine::config('use_email_notification'))
            return parent::deliver_mail($method_name, $args);
    }
    
    private function setup_email($user)
    {
        $this->to   = $user->email;
        $this->from = AclEngine::config('email_from');
        $this->subject = '['.AclEngine::config('app_name').'] ';
        $this->content_type = 'text/plain; charset=utf-8; format=flowed';
    }
}

?>