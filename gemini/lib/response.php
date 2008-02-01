<?php

class SResponse
{
    public $body         = null;
    public $assigns      = array();
    public $headers      = array();
    public $redirected_to = null;
    
    private static $default_headers = array('Cache-Control' => 'no_cache');
    
    public static $status_code_text = array(
        100 => 'CONTINUE',
        101 => 'SWITCHING PROTOCOLS',
        200 => 'OK',
        201 => 'CREATED',
        202 => 'ACCEPTED',
        203 => 'NON-AUTHORITATIVE INFORMATION',
        204 => 'NO CONTENT',
        205 => 'RESET CONTENT',
        206 => 'PARTIAL CONTENT',
        300 => 'MULTIPLE CHOICES',
        301 => 'MOVED PERMANENTLY',
        302 => 'FOUND',
        303 => 'SEE OTHER',
        304 => 'NOT MODIFIED',
        305 => 'USE PROXY',
        306 => 'RESERVED',
        307 => 'TEMPORARY REDIRECT',
        400 => 'BAD REQUEST',
        401 => 'UNAUTHORIZED',
        402 => 'PAYMENT REQUIRED',
        403 => 'FORBIDDEN',
        404 => 'NOT FOUND',
        405 => 'METHOD NOT ALLOWED',
        406 => 'NOT ACCEPTABLE',
        407 => 'PROXY AUTHENTICATION REQUIRED',
        408 => 'REQUEST TIMEOUT',
        409 => 'CONFLICT',
        410 => 'GONE',
        411 => 'LENGTH REQUIRED',
        412 => 'PRECONDITION FAILED',
        413 => 'REQUEST ENTITY TOO LARGE',
        414 => 'REQUEST-URI TOO LONG',
        415 => 'UNSUPPORTED MEDIA TYPE',
        416 => 'REQUESTED RANGE NOT SATISFIABLE',
        417 => 'EXPECTATION FAILED',
        500 => 'INTERNAL SERVER ERROR',
        501 => 'NOT IMPLEMENTED',
        502 => 'BAD GATEWAY',
        503 => 'SERVICE UNAVAILABLE',
        504 => 'GATEWAY TIMEOUT',
        505 => 'HTTP VERSION NOT SUPPORTED',
    );
    
    public function __construct()
    {
        $this->headers = array_merge($this->headers, self::$default_headers);
    }
    
    public function redirect($url, $permanently = false)
    {
        if ($this->headers['Status'] != '301 Moved Permanently')
            $this->headers['Status'] = '302 Found';
            
        $this->headers['location'] = $url;
        $this->body = "<html><body>You are being <a href=\"{$url}\">redirected</a>.</body></html>";
    }
    
    public function send_headers()
    {
        foreach($this->headers as $key => $value) header($key.': '.$value);
    }
    
    public function out()
	{
        $this->send_headers();
        echo $this->body;
    }
}

?>
