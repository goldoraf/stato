<?php

class AuthException extends Exception {}

class Cookie
{
    private $created;
    private $userId;
    private $version;
    
    private static $cookieName = 'USERAUTH';
    private static $myVersion  = '1';
    private static $expiration = '600';
    private static $resetTime  = '300';
    private static $glue       = '|';
    
    public function __construct($userId = False)
    {
        if ($userId)
        {
            $this->userId = $userId;
            return;
        }
        else
        {
            if (!array_key_exists(self::$cookieName, $_COOKIE))
                throw new AuthException('No cookie !');
                
            $this->unpackage($_COOKIE[self::$cookieName]);
        }
    }
    
    public function set()
    {
        set_cookie(self::$cookieName, $this->package());
    }
    
    public function logout()
    {
        set_cookie(self::$cookieName, '', 0);
    }
    
    public function validate()
    {
        if (!$this->version || !$this->created || !$this->userId)
            throw new AuthException('Malformed cookie !');
            
        if ($this->version != self::$myVersion)
            throw new AuthException('Version mismatch !');
            
        if (time() - $this->created > self::$expiration)
            throw new AuthException('Cookie expired !');
            
        if (time() - $this->created > self::$resetTime) $this->set();
    }
    
    private function package()
    {
        $parts = array(self::$myVersion, time(), $this->userId);
        $cookie = implode(self::$glue, $parts);
        return Encryption::encrypt($cookie);
    }
    
    private function unpackage($cookie)
    {
        $buffer = Encryption::decrypt($cookie);
        list($this->version, $this->created, $this->userId) = explode(self::$glue, $buffer);
    }
}

?>
