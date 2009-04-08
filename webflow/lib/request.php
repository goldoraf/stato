<?php

class Stato_InvalidHttpMethod extends Exception {}

/**
 * HTTP request class
 * 
 * @package Stato
 * @subpackage webflow
 */
class Stato_Request
{
    /**
     * Provides quick array access to GET, POST and userland parameters
     * @var Stato_RequestParams
     */
    public $params;
    
    /**
     * Provides quick array access to uploaded FILES
     * @var Stato_RequestFiles
     */
    public $files;
    
    /**
     * Allowed HTTP methods
     * @var array 
     */
    protected static $allowedMethods = array(
        'get', 'post', 'put', 'delete', 'head', 'options'
    );
    
    /**
     * Base url
     * @var string
     */
    protected $baseUrl = null;
    
    /**
     * Request URI
     * @var string
     */
    protected $requestUri = null;
    
    /**
     * Base path
     * @var string
     */
    protected $basePath = null;
    
    /**
     * Path info
     * @var string
     */
    protected $pathInfo = null;
    
    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct()
    {
        $this->params = new Stato_RequestParams();
        $this->files = new Stato_RequestFiles();
        if ($this->isPut()) $this->setParams($this->parseRawBodyParams());
    }
    
    /**
     * Returns values contained in the GET and POST superglobals
     * 
     * Order of precedence: 1. userland params, 2. GET, 3. POST
     * 
     * @return mixed
     */
    public function getParam($key)
    {
        return $this->params[$key];
    }
    
    /**
     * Returns an instance or an array of Stato_UploadedFile instances
     * 
     * @return mixed
     */
    public function getFiles($key)
    {
        return $this->files[$key];
    }
    
    /**
     * Set userland parameters
     *
     * @param array $params
     * @return void
     */
    public function setParams(array $params)
    {
        $this->params->merge($params);
    }
    
    /**
     * Is this a POST request ?
     * 
     * @return boolean
     */
    public function isPost()
    {
        return $this->getMethod() == 'post';
    }
    
    /**
     * Is this a GET request ?
     * 
     * @return boolean
     */
    public function isGet()
    {
        return $this->getMethod() == 'get';
    }
    
    /**
     * Is this a HEAD request ?
     * 
     * @return boolean
     */
    public function isHead()
    {
        return $this->getMethod() == 'head';
    }
    
    /**
     * Is this a PUT request ?
     * 
     * @return boolean
     */
    public function isPut()
    {
        return $this->getMethod() == 'put';
    }
    
    /**
     * Is this a DELETE request ?
     * 
     * @return boolean
     */
    public function isDelete()
    {
        return $this->getMethod() == 'delete';
    }
    
    /**
     * Is this a OPTIONS request ?
     * 
     * @return boolean
     */
    public function isOptions()
    {
        return $this->getMethod() == 'options';
    }
    
    /**
     * Is this an SSL request ?
     * 
     * @return boolean
     */
    public function isSecure()
    {
        return @$_SERVER['HTTPS'] == 'on';
    }
    
    /**
     * Returns the HTTP request method as a lowercase string
     * 
     * @return string
     */
    public function getMethod()
    {
        if (!isset($_SERVER['REQUEST_METHOD'])) return null;
        
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        
        if (!in_array($method, self::$allowedMethods))
            throw new Stato_InvalidHttpMethod($method);
        
        return $method;
    }
    
    /**
     * Returns the REQUEST_URI
     *
     * @return string
     */
    public function getRequestUri()
    {
        if ($this->requestUri === null) $this->setRequestUri();
        return $this->requestUri;
    }
    
    /**
     * Sets the REQUEST_URI
     *
     * If no request URI is passed, uses the value in $_SERVER['REQUEST_URI']
     * 
     * @return void
     */
    public function setRequestUri($requestUri = null)
    {
        if ($requestUri === null && isset($_SERVER['REQUEST_URI']))
            $requestUri = $_SERVER['REQUEST_URI'];
        $this->requestUri = $requestUri;
    }
    
    /**
     * Returns the segment of the url leading to the script name (e.g: /app/index.php)
     *
     * @return string
     */
    public function getBaseUrl()
    {
        if ($this->baseUrl === null) $this->setBaseUrl();
        return $this->baseUrl;
    }
    
    /**
     * Sets the base url of the request
     *
     * If no base url is passed, uses the value in $_SERVER['SCRIPT_NAME']
     * 
     * @return void
     */
    public function setBaseUrl($baseUrl = null)
    {
        if ($baseUrl === null) {
            $baseUrl = $_SERVER['SCRIPT_NAME'];
        
            $requestUri = $this->getRequestUri();
            if (strpos($requestUri, $baseUrl) !== 0) {
                if (strpos($requestUri, dirname($baseUrl)) === 0) 
                    $baseUrl = dirname($baseUrl);
                elseif (strpos($requestUri, $baseUrl) === false) 
                    $baseUrl = '';
            }
        }
        
        $this->baseUrl = rtrim($baseUrl, '/');
    }
    
    /**
     * Returns the base url minus the script name (e.g: /app)
     *
     * @return string
     */
    public function getBasePath()
    {
        if ($this->basePath === null) $this->setBasePath();
        return $this->basePath;
    }
    
    /**
     * Sets the base path
     * 
     * @return void
     */
    public function setBasePath($basePath = null)
    {
        if ($basePath === null)
            $basePath = str_replace(basename($_SERVER['SCRIPT_FILENAME']), '', $this->getBaseUrl());
        
        if ($basePath != '/') $basePath = rtrim($basePath, '/');
        $this->basePath = $basePath;
    }
    
    /**
     * Returns everything between the base url and the query string
     *
     * @return string
     */
    public function getPathInfo()
    {
        if ($this->pathInfo === null) $this->setPathInfo();
        return $this->pathInfo;
    }
    
    /**
     * Sets the path info
     * 
     * @return void
     */
    public function setPathInfo($pathInfo = null)
    {
        if ($pathInfo === null)
        {
            $requestUri = $this->getRequestUri();
            if (strpos($requestUri, '?') !== false) 
                list($pathInfo, ) = explode('?', $requestUri);
            else
                $pathInfo = $requestUri;
            
            $baseUrl = $this->getBaseUrl();
            if (!empty($baseUrl))
                $pathInfo = substr($pathInfo, strlen($baseUrl));
        }
        
        $this->pathInfo = $pathInfo;
    }
    
    /**
     * Returns the host for the request
     * 
     * @return string
     */
    public function getHost()
    {
        return $_SERVER['SERVER_NAME'];
    }
    
    /**
     * Returns the port number of the request
     * 
     * @return string
     */
    public function getPort()
    {
        return $_SERVER['SERVER_PORT'];
    }
    
    /**
     * Determines originating IP address, using REMOTE_ADDR header
     * 
     * @return string
     */
    public function getRemoteIp()
    {
        return $_SERVER['REMOTE_ADDR'];
    }
    
    /**
     * Return 'https://' if this is an SSL request and 'http://' otherwise
     * 
     * @return string
     */
    public function getProtocol()
    {
        return ($this->isSecure() ? 'https://' : 'http://');
    }
    
    /**
     * Returns the standard port number for the request's protocol
     * 
     * @return integer
     */
    public function getStandardPort()
    {
        return (($this->getProtocol() == 'https://') ? 443 : 80);
    }
    
    /**
     * Returns a port suffix like ":8080" if the port number of the request is not the default HTTP port 80 or HTTPS port 443
     * 
     * @return string
     */
    public function getPortString()
    {
        return (($this->getPort() == $this->getStandardPort()) ? '' : ':'.$this->getPort());
    }
    
    /**
     * Returns a host:port string for the request
     * 
     * @return string
     */
    public function getHostWithPort()
    {
        return $this->getHost().$this->getPortString();
    }
    
    /**
     * Returns the raw body of the request
     * 
     * @return string|false
     */
    public function rawBody()
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
    private function parseRawBodyParams()
    {
        $params = array();
        if (($data = $this->rawBody()) !== false) parse_str($data, $params);
        return $params;
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
class Stato_RequestParams implements ArrayAccess
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
 * corresponds, you will get either an instance of Stato_UploadedFile or 
 * an array of instances.
 * 
 * @package Stato
 * @subpackage webflow
 */
class Stato_RequestFiles implements ArrayAccess
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
        if (!isset($this->files[$key])) $this->files[$key] = $this->prepareFiles($_FILES[$key]);
        return $this->files[$key];
    }
    
    public function offsetSet($key, $file)
    {
        if (!$file instanceof Stato_UploadedFile)
            throw new Exception('Only Stato_UploadedFile instances can be added to a Stato_RequestFiles instance');
        
        $this->files[$key] = $file;
    }
    
    public function offsetUnset($key)
    {
        if (array_key_exists($key, $this->files)) unset($this->params[$key]);
    }
    
    public function prepareFiles($files)
    {
        if (!is_array($files['name'])) {
            return new Stato_UploadedFile($files['tmp_name'], $files['name'], 
                                          $files['type'], $files['size'], $files['error']);
        } else {
            $uploads = array();
            foreach ($files['name'] as $k => $v) {
                $uploads[] = new Stato_UploadedFile($files['tmp_name'][$k], $v, 
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
class Stato_UploadedFile
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
    
    protected $originalError;
    
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
        $this->originalError = $error;
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
    public function isSafe()
    {
        return is_uploaded_file($this->tmp);
    }
    
    /**
     * Tries to get the real file mimetype. It uses the fileinfo extension if 
     * it is available, or uses the mimetype given by the fileserver.
     */
    public function getMimeType()
    {
        if (!class_exists('finfo', false)) return $this->type;
        $info = new finfo(FILEINFO_MIME);
        return $info->file($this->tmp);
    }
    
    /**
     * Returns the original error constant given by PHP
     */
    public function getOriginalError()
    {
        return $this->originalError;
    }
}