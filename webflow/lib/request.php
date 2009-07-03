<?php

class SUnknownHttpMethod extends Exception {}
/**
 * Request class
 * 
 * @package Stato
 * @subpackage webflow
 */
class SRequest
{
    /**
     * Provides quick array access to GET, POST and userland parameters
     * @var SRequestParams
     */
    public $params;
    
    /**
     * Provides quick array access to uploaded FILES
     * @var SRequestFiles
     */
    public $files;
    
    /**
     * Allowed HTTP methods
     * @var array 
     */
    public static $accepted_http_methods = array('get', 'post', 'put', 'delete', 'head', 'options');
    
    protected $base_url = null;
    protected $request_uri = null;
    protected $base_path = null;
    protected $path_info = null;
    protected $accepts;
    protected $format;
    
    public function __construct()
    {
        $this->params = new SRequestParams();
        $this->files = new SRequestFiles();
        if ($this->is_put()) $this->inject_params($this->parse_raw_body_params());
    }
    
    public function inject_params($params)
    {
        $this->params->merge($params);
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
        {
            $accepts = $this->accepts();
            $this->format = array_shift($accepts);
        }
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
     * Returns the raw body of the request
     * 
     * @return string|false
     */
    public function raw_body()
    {
        $data = file_get_contents('php://input');
        if (strlen(trim($data)) > 0) return $data;
        return false;
    }
    
    /**
     * Extracts params from the raw body of the request, if present.
     * 
     * @return array
     */
    private function parse_raw_body_params()
    {
        $params = array();
        if (($data = $this->raw_body()) !== false) parse_str($data, $params);
        return $params;
    }
    
    /**
     * Returns the REQUEST_URI
     *
     * @return string
     */
    public function request_uri()
    {
        if ($this->request_uri === null) $this->set_request_uri();
        return $this->request_uri;
    }
    
    /**
     * Sets the REQUEST_URI
     *
     * If no request URI is passed, uses the value in $_SERVER['REQUEST_URI']
     * 
     * @return void
     */
    public function set_request_uri($request_uri = null)
    {
        if ($request_uri === null && isset($_SERVER['REQUEST_URI']))
            $request_uri = $_SERVER['REQUEST_URI'];
        $this->request_uri = $request_uri;
    }
    
    /**
     * Returns the segment of the url leading to the script name (e.g: /app/index.php)
     *
     * @return string
     */
    public function base_url()
    {
        if ($this->base_url === null) $this->set_base_url();
        return $this->base_url;
    }
    
    /**
     * Sets the base url of the request
     *
     * If no base url is passed, uses the value in $_SERVER['SCRIPT_NAME']
     * 
     * @return void
     */
    public function set_base_url($base_url = null)
    {
        if ($base_url === null) {
            $base_url = $_SERVER['SCRIPT_NAME'];
        
            $request_uri = $this->request_uri();
            if (strpos($request_uri, $base_url) !== 0) {
                if (strpos($request_uri, dirname($base_url)) === 0) 
                    $base_url = dirname($base_url);
                elseif (strpos($request_uri, $base_url) === false) 
                    $base_url = '';
            }
        }
        
        $this->base_url = rtrim($base_url, '/');
    }
    
    /**
     * Returns the base url minus the script name (e.g: /app)
     *
     * @return string
     */
    public function base_path()
    {
        if ($this->base_path === null) $this->set_base_path();
        return $this->base_path;
    }
    
    /**
     * Sets the base path
     * 
     * @return void
     */
    public function set_base_path($base_path = null)
    {
        if ($base_path === null)
            $base_path = str_replace(basename($_SERVER['SCRIPT_FILENAME']), '', $this->base_url());
        
        $this->base_path = rtrim($base_path, '/');
    }
    
    /**
     * Returns everything between the base url and the query string
     *
     * @return string
     */
    public function path_info()
    {
        if ($this->path_info === null) $this->set_path_info();
        return $this->path_info;
    }
    
    /**
     * Sets the path info
     * 
     * @return void
     */
    public function set_path_info($path_info = null)
    {
        if ($path_info === null)
        {
            $request_uri = $this->request_uri();
            if (strpos($request_uri, '?') !== false) 
                list($path_info, ) = explode('?', $request_uri);
            else
                $path_info = $request_uri;
            
            $base_url = $this->base_url();
            if (!empty($base_url))
                $path_info = substr($path_info, strlen($base_url));
        }
        
        $extension = strrchr($path_info, '.');
        if ($extension !== false)
        {
            $this->set_format_by_extension(substr($extension, 1));
            $path_info = substr($path_info, 0, - strlen($extension));
        }
        
        $this->path_info = $path_info;
    }
}

/**
 * Wraps values contained in the GET and POST superglobals
 * 
 * Implements ArrayAccess for an easy access to values. You can also push
 * your own params in it, it will not overwrite values contained in superglobals.
 * As a same key can be present in the 3 sources of values, there is an order 
 * of precedence when you try to access a value: 1. userland params, 2. GET, 3. POST
 * 
 * @package Stato
 * @subpackage webflow
 */
class SRequestParams implements ArrayAccess
{
    /**
     * Userland parameters
     * @var array
     */
    protected $params;
    
    public function __construct()
    {
        $this->params = array();
    }
    
    public function merge($params)
    {
        $this->params = array_merge($this->params, $params);
    }
    
    public function offsetExists($key)
    {
        return isset($this->params[$key])
            || isset($_GET[$key])
            || isset($_POST[$key]);
    }
    
    public function offsetGet($key)
    {
        switch (true) {
            case isset($this->params[$key]):
                return $this->params[$key];
            case isset($_GET[$key]):
                return $_GET[$key];
            case isset($_POST[$key]):
                return $_POST[$key];
            default:
                return null;
        }
    }
    
    public function offsetSet($key, $value)
    {
        $this->params[$key] = $value;
    }
    
    public function offsetUnset($key)
    {
        if (array_key_exists($key, $this->params)) unset($this->params[$key]);
    }
}

/**
 * Wraps uploaded files contained in the FILES superglobal
 * 
 * Implements ArrayAccess for an easy access to files. When you try to 
 * retrieve a particular key and there is/are uploaded file(s) that 
 * corresponds, you will get either an instance of SUploadedFile or 
 * an array of instances.
 * 
 * @package Stato
 * @subpackage webflow
 */
class SRequestFiles implements ArrayAccess
{
    protected $files;
    
    public function __construct()
    {
        $this->files = array();
    }
    
    public function offsetExists($key)
    {
        return isset($this->files[$key]) || isset($_FILES[$key]);
    }
    
    public function offsetGet($key)
    {
        if (!$this->offsetExists($key)) return null;
        if (!isset($this->files[$key])) $this->files[$key] = $this->prepare_files($_FILES[$key]);
        return $this->files[$key];
    }
    
    public function offsetSet($key, $file)
    {
        if (!$file instanceof SUploadedFile)
            throw new Exception('Only SUploadedFile instances can be added to a SRequestFiles instance');
        
        $this->files[$key] = $file;
    }
    
    public function offsetUnset($key)
    {
        if (array_key_exists($key, $this->files)) unset($this->params[$key]);
    }
    
    public function prepare_files($files)
    {
        if (!is_array($files['name'])) {
            return new SUploadedFile($files['tmp_name'], $files['name'], 
                                          $files['type'], $files['size'], $files['error']);
        } else {
            $uploads = array();
            foreach ($files['name'] as $k => $v) {
                $uploads[$k] = new SUploadedFile($files['tmp_name'][$k], $v, 
                                                 $files['type'][$k], $files['size'][$k], $files['error'][$k]);
            }
            return $uploads;
        }
    }
}

/**
 * Represents a file upload.
 * 
 * @package Stato
 * @subpackage webflow
 */
class SUploadedFile
{
    const SIZE = 'size';
    const PARTIAL = 'partial';
    const NO_FILE = 'no_file';
    const SYSTEM = 'system';
    
    public $name;
    public $size;
    public $type;
    public $error;
    public $tmp;
    
    protected $original_error;
    
    public function __construct($tmp, $name, $type, $size, $error)
    {
        $this->name = $name;
        $this->tmp = $tmp;
        $this->type = $type;
        $this->size = $size;
        switch ($error) {
            case UPLOAD_ERR_OK:
                $this->error = false;
                break;
            case UPLOAD_ERR_INI_SIZE:
                $this->error = self::SIZE;
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $this->error = self::SIZE;
                break;
            case UPLOAD_ERR_PARTIAL:
                $this->error = self::PARTIAL;
                break;
            case UPLOAD_ERR_NO_FILE:
                $this->error = self::NO_FILE;
                break;
            default:
                $this->error = self::SYSTEM;
        }
        $this->original_error = $error;
    }
    
    /**
     * Moves the uploaded file to a new location after checking that the 
     * file is safe.
     */
    public function move($path)
    {
        return move_uploaded_file($this->tmp, $path);
    }
    
    /**
     * Tells whether the file was actually uploaded via HTTP POST.
     * This is useful to help ensure that a malicious user hasn't tried 
     * to trick your app to gain access to sensible files.
     */
    public function is_safe()
    {
        return is_uploaded_file($this->tmp);
    }
    
    /**
     * Tries to get the real file mimetype. It uses the fileinfo extension if 
     * it is available, or uses the mimetype given by the fileserver.
     */
    public function get_mime_type()
    {
        if (!class_exists('finfo', false)) return $this->type;
        $info = new finfo(FILEINFO_MIME);
        return $info->file($this->tmp);
    }
    
    /**
     * Returns the original error constant given by PHP
     */
    public function get_original_error()
    {
        return $this->original_error;
    }
}