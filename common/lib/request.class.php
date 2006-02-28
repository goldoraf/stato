<?php

class SRequest
{
    public $module     = Null;
    public $controller = Null;
    public $action     = Null;
    public $params     = array();
    
    private $relativeUrlRoot = Null;
    private $requestUri      = Null;
    private $host            = Null;
    
    public function __construct()
    {
        if ($this->isPost()) $this->parsePostParameters();
    }
    
    public function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }
    
    public function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] == 'GET';
    }
    
    public function isSSL()
    {
        return $_SERVER['HTTPS'] == 'on';
    }
    
    public function host()
    {
        return $_SERVER['SERVER_NAME'];
    }
    
    public function port()
    {
        return $_SERVER['SERVER_PORT'];
    }
    
    public function protocol()
    {
        return ($this->isSSL() ? 'https://' : 'http://');
    }
    
    public function standardPort()
    {
        return (($this->protocol() == 'https://') ? 443 : 80);
    }
    
    public function portString()
    {
        return (($this->port() == $this->standardPort()) ? '' : ':'.$this->port());
    }
    
    public function hostWithPort()
    {
        return $this->host().$this->portString();
    }
    
    public function requestUri()
    {
        if (!isset($this->requestUri))
            $this->requestUri = substr($_SERVER['REQUEST_URI'], strlen($this->relativeUrlRoot()));
        return $this->requestUri;
    }
    
    public function relativeUrlRoot()
    {
        if (!isset($this->relativeUrlRoot))
            $this->relativeUrlRoot = str_replace('/index.php', '/', $_SERVER['SCRIPT_NAME']);
        return $this->relativeUrlRoot;
    }
    
    private function parsePostParameters()
    {
        $keys  = array_keys($_POST);
        $count = sizeof($keys);
        for ($i = 0; $i < $count; $i++) $this->params[$keys[$i]] = $_POST[$keys[$i]];
        
		$keys  = array_keys($_FILES);
        $count = sizeof($keys);
        for ($i = 0; $i < $count; $i++)
		{
        	if (is_array($_FILES[$keys[$i]]['name']))
        	{
                $subkeys = array_keys($_FILES[$keys[$i]]['name']);
                foreach($subkeys as $subkey)
                {
                    if ($_FILES[$keys[$i]]['error'][$subkey] != UPLOAD_ERR_NO_FILE)
                	{
                        $flags = array('name', 'type', 'tmp_name', 'error', 'size');
                        foreach($flags as $flag) $file[$flag] = $_FILES[$keys[$i]][$flag][$subkey];
                        $this->params[$keys[$i]][$subkey] = new SUpload($file);
                    }
                    else $this->params[$keys[$i]][$subkey] = False;
                }
            }
            else
            {
                if ($_FILES[$keys[$i]]['error'] != UPLOAD_ERR_NO_FILE)
                    $this->params[$keys[$i]] = new SUpload($_FILES[$keys[$i]]);
                else
                    $this->params[$keys[$i]] = False;
            }
        }
	}
}

?>
