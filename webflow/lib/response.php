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
     * Returns response body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
    
    /**
     * Sets HTTP status code
     *
     * @param int $code
     * @return void
     */
    public function setStatus($code)
    {
        if (!is_int($code) || ($code < 100) || ($code > 599))
            throw new Stato_InvalidHttpStatusCode($code);
        $this->status = $code;
    }
    
    /**
     * Returns HTTP status code
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
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
