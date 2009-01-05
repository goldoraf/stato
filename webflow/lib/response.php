<?php

class Stato_InvalidHttpStatusCode extends Exception {}

/**
 * HTTP response class
 * 
 * @package Stato
 * @subpackage webflow
 */
class Stato_Response
{
    /**
     * Standard HTTP status codes
     * @var array
     */
    protected static $statusCodes = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        226 => 'IM Used',
        
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        507 => 'Insufficient Storage',
        510 => 'Not Extended'
    );
    
    /**
     * Body content
     * @var string
     */
    protected $body = '';
    
    /**
     * Array of headers
     * @var array
     */
    protected $headers = array();
    
    /**
     * HTTP status code to use in headers
     * @var int
     */
    protected $status = 200;
    
    /**
     * Sets response body
     *
     * @param string $body
     * @return void
     */
    public function setBody($body)
    {
        $this->body = $body;
    }
    
    /**
     * Sets HTTP status code
     *
     * @param int $code
     * @return void
     */
    public function setStatus($code)
    {
        if (!array_key_exists($code, self::$statusCodes)) 
            throw new Stato_InvalidHttpStatusCode($code);
        $this->status = $code;
    }
    
    /**
     * Returns HTTP status code and his associated text
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status.' '.self::$statusCodes[$this->status];
    }
    
    /**
     * Sets a header
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }
    
    /**
     * Returns all headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    
    /**
     * Prepares for redirection to an url
     *
     * Sets Location header and response code.
     *
     * @param string $url
     * @param boolean $permanently
     * @return void
     */
    public function redirect($url, $permanently = false)
    {
        $this->setStatus(($permanently) ? 301 : 302);
        $this->setHeader('Location', $url);
        $this->setBody("<html><body>You are being <a href=\"{$url}\">redirected</a>.</body></html>");
    }
    
    /**
     * Sends all headers
     *
     * @return void
     */
    public function sendHeaders()
    {
        foreach($this->getHeaders() as $key => $value) header($key.': '.$value);
    }
    
    /**
     * Sends the status header
     *
     * @return void
     */
    public function sendStatus()
    {
        header('HTTP/1.x '.$this->getStatus());
    }
    
    /**
     * Sends the response, including all headers
     *
     * @return void
     */
    public function send()
    {
        $this->sendStatus();
        $this->sendHeaders();
        echo $this->body;
    }
}
