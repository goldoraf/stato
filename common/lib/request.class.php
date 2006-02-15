<?php

class SRequest
{
    const METHOD_POST = 1;
    const METHOD_GET  = 2;
    
    public $uri        = Null;
    public $host       = Null;
    public $module     = Null;
    public $controller = Null;
    public $action     = Null;
    public $method     = Null;
    public $params     = array();
    public $relativeUrlRoot = Null;
    
    public function __construct()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') $this->method = self::METHOD_POST;
        elseif ($_SERVER['REQUEST_METHOD'] == 'GET') $this->method = self::METHOD_GET;
        
        $this->relativeUrlRoot = $this->relativeUrlRoot();
        $this->uri = $this->extractPath();
        $this->host = $_SERVER['SERVER_NAME'];
        
        $this->parseUrl();
        $this->parseParameters();
    }
    
    public function isPost()
    {
        return $this->method == self::METHOD_POST;
    }
    
    public function isGet()
    {
        return $this->method == self::METHOD_GET;
    }
    
    private function parseUrl()
    {
        $options = SRoutes::parseUrl($this->uri);
        $this->module     = $options['module'];
        $this->controller = $options['controller'];
        $this->action     = $options['action'];
        $this->params     = $options;
    }
    
    private function extractPath()
    {
        return substr($_SERVER['REQUEST_URI'], strlen($this->relativeUrlRoot));
    }
    
    private function relativeUrlRoot()
    {
        return str_replace('/index.php', '/', $_SERVER['SCRIPT_NAME']);
    }
    
    private function parseParameters()
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
