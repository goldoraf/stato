<?php

class SRequest
{
    public $controller = Null;
    public $action     = Null;
    public $params     = array();
    
    private $relative_url_root = Null;
    private $request_uri      = Null;
    private $host            = Null;
    
    public function __construct()
    {
        if ($this->is_post()) $this->parse_post_parameters();
    }
    
    public function is_post()
    {
        return $this->method() == 'POST';
    }
    
    public function is_get()
    {
        return $this->method() == 'GET';
    }
    
    public function is_ssl()
    {
        return @$_SERVER['HTTPS'] == 'on';
    }
    
    public function is_xml_http_request()
    {
        if (preg_match('/XMLHttpRequest/i', @$_SERVER['HTTP_X_REQUESTED_WITH'])) return true;
        return false;
    }
    
    public function is_xhr()
    {
        return $this->is_xml_http_request();
    }
    
    public function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    public function host()
    {
        return $_SERVER['SERVER_NAME'];
    }
    
    public function port()
    {
        return $_SERVER['SERVER_PORT'];
    }
    
    public function remote_ip()
    {
        return $_SERVER['REMOTE_ADDR'];
    }
    
    public function protocol()
    {
        return ($this->is_ssl() ? 'https://' : 'http://');
    }
    
    public function standard_port()
    {
        return (($this->protocol() == 'https://') ? 443 : 80);
    }
    
    public function port_string()
    {
        return (($this->port() == $this->standard_port()) ? '' : ':'.$this->port());
    }
    
    public function host_with_port()
    {
        return $this->host().$this->port_string();
    }
    
    public function raw_post_data()
    {
        return file_get_contents('php://input');
    }
    
    public function request_uri()
    {
        if (!isset($this->request_uri))
            $this->request_uri = substr($_SERVER['REQUEST_URI'], strlen($this->relative_url_root()));
        return $this->request_uri;
    }
    
    public function relative_url_root()
    {
        if (!isset($this->relative_url_root))
            $this->relative_url_root = str_replace('/index.php', '/', $_SERVER['SCRIPT_NAME']);
        return $this->relative_url_root;
    }
    
    private function parse_post_parameters()
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
