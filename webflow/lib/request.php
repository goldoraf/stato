<?php

class Stato_UnknownHttpMethod extends Exception {}

/**
 * HTTP request class
 * 
 * @package Stato
 * @subpackage webflow
 */
class Stato_Request
{
    /**
     * Allowed HTTP methods
     * @var array 
     */
    protected static $allowedMethods = array(
        'get', 'post', 'put', 'delete', 'head', 'options'
    );
    
    /**
     * Userland parameters
     * @var array
     */
    protected $params = array();
    
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
     * @param string $requestUri
     * @return void
     */
    public function __construct($requestUri = null)
    {
        if ($this->isPut()) $this->setParams($this->parseRawBodyParams());
        $this->setRequestUri($requestUri);
    }
    
    /**
     * Access params as public properties
     * 
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getParam($key);
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
    
    /**
     * Set userland parameters
     *
     * @param array $params
     * @return void
     */
    public function setParams(array $params)
    {
        $this->params = array_merge($this->params, $params);
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
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        
        if (!in_array($method, self::$allowedMethods))
            throw new Stato_UnknownHttpMethod($method);
        
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
        if ($requestUri === null) $requestUri = $_SERVER['REQUEST_URI'];
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
        if ($baseUrl === null) $baseUrl = $_SERVER['SCRIPT_NAME'];
        $this->baseUrl = $baseUrl;
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
        
        $this->basePath = rtrim($basePath, '/');
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
                list($requestUri, ) = explode('?', $requestUri);
            
            $pathInfo = substr($requestUri, strlen($this->getBaseUrl()));
        }
        
        $this->pathInfo = ltrim((string) $pathInfo, '/');
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
