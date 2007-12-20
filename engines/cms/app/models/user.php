<?php

class User extends SActiveRecord
{
    public static $objects;
    
    public function validate()
    {
        $this->validate_presence_of('lastname', 'firstname', 'email', 'login', 'password');
        
        $this->validate_format_of('password', array('pattern' => 'alphanum', 
                                                    'message' => 'seuls les caractères alphanumériques sont autorisés !'));
        $this->validate_length_of('password', array('min_length' => 6, 'max_length' => 15, 
                                                    'wrong_size' => 'entre 6 et 15 caractères SVP !'));
        $this->validate_confirmation_of('password', array('on' => 'create', 
                                                          'message' => 'les 2 saisies du mot de passe ne coincident pas !'));
    }
    
    public static function authenticate($login, $password)
    {
        try {
            return self::$objects->get('login = :login', 'password = :password', 
                                        array(':login' => $login, ':password' => $password));                      
        } catch (SActiveRecordDoesNotExist $e) {
            return false;
        }
    }
}

?>
