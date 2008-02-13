<?php

class User extends SActiveRecord
{
    public static $objects;
    public static $relationships = array('roles' => 'many_to_many');
    public static $table_name = null;
    public $record_timestamps = true;
    public $password = null;
    public $new_password = false;
    
    public function __repr()
    {
        return $this->firstname.' '.$this->lastname;
    }
    
    public function validate()
    {
        $this->validate_presence_of(array('login', 'email'));
        
        $this->validate_length_of('login', array('min_length' => 3, 'max_length' => 60));
        $this->validate_format_of('email', array('pattern' => 'email'));
        
        $this->validate_uniqueness_of('login');
        $this->validate_uniqueness_of('email');
        
        if ($this->new_password)
        {
            $this->validate_presence_of('password');
            $this->validate_confirmation_of('password');
            $this->validate_length_of('password', array('min_length' => 5, 'max_length' => 60));
        }
    }
    
    public function after_validate()
    {
        $this->crypt_password();
    }
    
    public function after_save()
    {
        $this->new_password = false;
    }
    
    public function change_password($pass, $confirm = null)
    {
        $this->password = $pass;
        $this->values['password_confirmation'] = (($confirm === null) ? $pass : $confirm);
        $this->new_password = true;
    }
    
    public function generate_security_token($hours = null)
    {
        if ($hours === null) $hours = AclEngine::config('security_token_life_hours');
        $this->security_token = AclEngine::hashed($this->password.time().rand());
        $this->token_expiry = SDateTime::at(SDateTime::now()->ts() + $hours * 60 * 60);
        $this->save();
        return $this->security_token;
    }
    
    public function is_token_expired()
    {
        return ($this->security_token !== null && $this->token_expiry !== null
            && SDateTime::now()->ts() > $this->token_expiry->ts());
    }
    
    public function set_as_verified()
    {
        // faut-il aussi passer à null le token et son expiry ???
        $this->verified = true;
        $this->save();
    }
    
    public function set_delete_after()
    {
        $hours = AclEngine::config('delayed_delete_days') * 24;
        $this->deleted = true;
        $this->delete_after = SDateTime::at(SDateTime::now()->ts() + $hours * 60 *60);
        return $this->generate_security_token($hours);
    }
    
    public function is_superuser()
    {
        foreach ($this->roles->all() as $r)
            if ($r->omnipotent) return true;
        
        return false;
    }
    
    private function crypt_password()
    {
        if (!$this->new_password) return;
        
        $this->salt = AclEngine::salt();
        $this->salted_password = AclEngine::salted_password($this->salt, AclEngine::hashed($this->password));
    }
}

User::$table_name = AclEngine::config('users_table');

?>
