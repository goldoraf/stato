<?php

class SUnknownHttpMethod extends Exception {}
/**
 * Request class
 * 
 * @package Stato
 * @subpackage gemini
 */
class SRequest
{
    /**
     * Holds both GET and POST parameters in a single array. Uploaded files are held 
     * in an instance of SUpload class     
     */
    public $params = array();
    
    private static $accepted_http_methods = array('get', 'post', 'put', 'delete', 'head', 'options');
    
    private $relative_url_root;
    private $request_uri;
    private $accepts;
    private $format;
    
    public function __construct()
    {
        if ($this->is_post()) $this->parse_post_parameters();
    }
    
    public function inject_params($params)
    {
        $this->params = array_merge($this->params, $params);
    }
    
    /**
     * Is this a POST request ?
     */
    public function is_post()
    {
        return $this->method() == 'post';
    }
    
    /**
     * Is this a GET request ?
     */
    public function is_get()
    {
        return $this->method() == 'get';
    }
    
    /**
     * Is this a HEAD request ?
     */
    public function is_head()
    {
        return $this->method() == 'head';
    }
    
    /**
     * Is this a PUT request ?
     */
    public function is_put()
    {
        return $this->method() == 'put';
    }
    
    /**
     * Is this a DELETE request ?
     */
    public function is_delete()
    {
        return $this->method() == 'delete';
    }
    
    /**
     * Is this an SSL request ?
     */
    public function is_ssl()
    {
        return @$_SERVER['HTTPS'] == 'on';
    }
    
    /**
     * Returns true if the request's "X-Requested-With" header contains "XMLHttpRequest".
     * (The Prototype Javascript library sends this header with every Ajax request.)
     */
    public function is_xml_http_request()
    {
        if (preg_match('/XMLHttpRequest/i', @$_SERVER['HTTP_X_REQUESTED_WITH'])) return true;
        return false;
    }
    
    /**
     * Alias for is_xml_http_request()
     */
    public function is_xhr()
    {
        return $this->is_xml_http_request();
    }
    
    /**
     * Returns the HTTP request method as a lowercase string
     */
    public function method()
    {
        $method = ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($this->params['_method']))
                ? $this->params['_method']
                : strtolower($_SERVER['REQUEST_METHOD']);
        
        if (!in_array($method, self::$accepted_http_methods))
            throw new SUnknownHttpMethod($method);
        
        return $method;
    }
    
    /**
     * Returns the accepted MIME types for the request
     */
    public function accepts()
    {
        if (!isset($this->accepts))
        {
            if (isset($_SERVER['HTTP_ACCEPT']) && !empty($_SERVER['HTTP_ACCEPT']))
                $this->accepts = SMimeType::parse($_SERVER['HTTP_ACCEPT']);
            else
                return array('all');
        }
        return $this->accepts;
    }
    
    /**
     * Returns the MIME type for the format used in the request.
     * If there is no format available, the first of the accept types will be used.
     */
    public function format()
    {
        if (!isset($this->format))
            $this->format = array_shift($this->accepts());
        return $this->format;
    }
    
    /**
     * Sets the format manually ; can be useful to force custom formats
     */
    public function set_format($format)
    {
        $this->format = $format;
    }
    
    /**
     * Sets the format by string extension ; used by routing
     */
    public function set_format_by_extension($extension)
    {
        $this->format = SMimeType::lookup_by_extension($extension);
    }
    
    /**
     * Returns the host for the request
     */
    public function host()
    {
        return $_SERVER['SERVER_NAME'];
    }
    
    /**
     * Returns the port number of the request
     */
    public function port()
    {
        return $_SERVER['SERVER_PORT'];
    }
    
    /**
     * Determine originating IP address, using REMOTE_ADDR header
     */
    public function remote_ip()
    {
        return $_SERVER['REMOTE_ADDR'];
    }
    
    /**
     * Return 'https://' if this is an SSL request and 'http://' otherwise
     */
    public function protocol()
    {
        return ($this->is_ssl() ? 'https://' : 'http://');
    }
    
    /**
     * Returns the standard port number for the request's protocol
     */
    public function standard_port()
    {
        return (($this->protocol() == 'https://') ? 443 : 80);
    }
    
    /**
     * Returns a port suffix like ":8080" if the port number of the request is not the default HTTP port 80 or HTTPS port 443
     */
    public function port_string()
    {
        return (($this->port() == $this->standard_port()) ? '' : ':'.$this->port());
    }
    
    /**
     * Returns a host:port string for the request
     */
    public function host_with_port()
    {
        return $this->host().$this->port_string();
    }
    
    /**
     * Returns the raw post data
     */
    public function raw_post_data()
    {
        return file_get_contents('php://input');
    }
    
    /**
     * Return the request URI
     */
    public function request_uri()
    {
        if (!isset($this->request_uri))
            $this->request_uri = substr($_SERVER['REQUEST_URI'], strlen($this->relative_url_root()));
        return $this->request_uri;
    }
    
    /**
     * Returns the path minus the web server relative installation directory
     */
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
