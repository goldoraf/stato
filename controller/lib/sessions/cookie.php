<?php

class SAuthException extends Exception {}

class SCookie
{
    private $created;
    private $user_id;
    private $version;
    
    private static $cookie_name = 'USERAUTH';
    private static $my_version  = '1';
    private static $expiration = '600';
    private static $reset_time  = '300';
    private static $glue       = '|';
    
    public function __construct($user_id = False)
    {
        if ($user_id)
        {
            $this->user_id = $user_id;
            return;
        }
        else
        {
            if (!array_key_exists(self::$cookie_name, $_cookie))
                throw new SAuthException('No cookie !');
                
            $this->unpackage($_cookie[self::$cookie_name]);
        }
    }
    
    public function set()
    {
        set_cookie(self::$cookie_name, $this->package());
    }
    
    public function logout()
    {
        set_cookie(self::$cookie_name, '', 0);
    }
    
    public function validate()
    {
        if (!$this->version || !$this->created || !$this->user_id)
            throw new SAuthException('Malformed cookie !');
            
        if ($this->version != self::$my_version)
            throw new SAuthException('Version mismatch !');
            
        if (time() - $this->created > self::$expiration)
            throw new SAuthException('Cookie expired !');
            
        if (time() - $this->created > self::$reset_time) $this->set();
    }
    
    private function package()
    {
        $parts = array(self::$my_version, time(), $this->user_id);
        $cookie = implode(self::$glue, $parts);
        return SEncryption::encrypt($cookie);
    }
    
    private function unpackage($cookie)
    {
        $buffer = SEncryption::decrypt($cookie);
        list($this->version, $this->created, $this->user_id) = explode(self::$glue, $buffer);
    }
}

?>
