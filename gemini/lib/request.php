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
    
    public static $accepted_http_methods = array('get', 'post', 'put', 'delete', 'head', 'options');
    
    private $relative_url_root;
    private $request_uri;
    private $accepts;
    private $format;
    
    public function __construct()
    {
        $this->parse_request_parameters();
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
     * Sets the format by string extension
     */
    public function set_format_by_extension($extension)
    {
        $this->format = SMimeType::lookup_by_extension($extension)->name;
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
     * Return the request URI minus file extension and querystring (for use with routing)
     * If a file extension is present, sets the format too
     */
    public function request_path()
    {
        $path = $this->request_uri();
        if (strpos($path, '?') !== false) list($path, ) = explode('?', $path);
        
        // Skip trailing slash
        if (substr($path, -1) == '/') $path = substr($path, 0, -1);
        
        $extension = strrchr($path, '.');
        if ($extension !== false)
        {
            $this->set_format_by_extension(substr($extension, 1));
            $path = substr($path, 0, - strlen($extension));
        }
        
        return $path;
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
    
    private function parse_request_parameters()
    {
        $this->params += $_GET;
        if ($this->is_put()) {
            $put_params = array();
            parse_str($this->raw_post_data(), $put_params);
            $this->params += $put_params;
        } elseif ($this->is_post()) {
            $this->params += $_POST;
            $this->extract_uploaded_files();
        }
    }
    
    private function extract_uploaded_files()
    {
        foreach ($_FILES as $key => $value) {
        	if (is_array($value['name'])) {
                foreach ($value['name'] as $k => $v)
                    if ($value['error'][$k] === UPLOAD_ERR_OK)
                        $this->params[$key][$k] = new SUploadedFile($value['tmp_name'][$k], $v, $value['type'][$k]);
            }
            elseif ($value['error'] === UPLOAD_ERR_OK)
                $this->params[$key] = new SUploadedFile($value['tmp_name'], $value['name'], $value['type']);
        }
	}
}

class SUploadedFile
{
    public $original_filename;
    public $content_type;
    
    private $temp_filename;
    
    public function __construct($temp_filename, $orig_filename, $content_type)
    {
        $this->temp_filename = $temp_filename;
        $this->original_filename = $orig_filename;
        $this->content_type = $content_type;
    }
    
    public function save_as($path, $chmod = null)
    {
        $mv_success = @move_uploaded_file($this->temp_filename, $path);
        if ($chmod === null) return $mv_success;
        return ($mv_success && @chmod($path, $chmod));
    }
}

?>
