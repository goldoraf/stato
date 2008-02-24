<?php

class SResponse
{
    public $body         = null;
    public $status       = null;
    public $assigns      = array();
    public $headers      = array();
    public $redirected_to = null;
    
    private static $default_headers = array('Cache-Control' => 'no_cache');
    
    public static $status_code_text = array(
        100 => "Continue",
        101 => "Switching Protocols",
        102 => "Processing",
        
        200 => "OK",
        201 => "Created",
        202 => "Accepted",
        203 => "Non-Authoritative Information",
        204 => "No Content",
        205 => "Reset Content",
        206 => "Partial Content",
        207 => "Multi-Status",
        226 => "IM Used",
        
        300 => "Multiple Choices",
        301 => "Moved Permanently",
        302 => "Found",
        303 => "See Other",
        304 => "Not Modified",
        305 => "Use Proxy",
        307 => "Temporary Redirect",
        
        400 => "Bad Request",
        401 => "Unauthorized",
        402 => "Payment Required",
        403 => "Forbidden",
        404 => "Not Found",
        405 => "Method Not Allowed",
        406 => "Not Acceptable",
        407 => "Proxy Authentication Required",
        408 => "Request Timeout",
        409 => "Conflict",
        410 => "Gone",
        411 => "Length Required",
        412 => "Precondition Failed",
        413 => "Request Entity Too Large",
        414 => "Request-URI Too Long",
        415 => "Unsupported Media Type",
        416 => "Requested Range Not Satisfiable",
        417 => "Expectation Failed",
        422 => "Unprocessable Entity",
        423 => "Locked",
        424 => "Failed Dependency",
        426 => "Upgrade Required",
        
        500 => "Internal Server Error",
        501 => "Not Implemented",
        502 => "Bad Gateway",
        503 => "Service Unavailable",
        504 => "Gateway Timeout",
        505 => "HTTP Version Not Supported",
        507 => "Insufficient Storage",
        510 => "Not Extended"
    );
    
    public function __construct()
    {
        $this->headers = array_merge($this->headers, self::$default_headers);
    }
    
    public function redirect($url, $permanently = false)
    {
        if ($permanently) $this->status = 301;
        else $this->status = 302;
            
        $this->headers['location'] = $url;
        $this->body = "<html><body>You are being <a href=\"{$url}\">redirected</a>.</body></html>";
    }
    
    public function send_headers()
    {
        if (!isset($this->headers['Status']) && isset(self::$status_code_text[$this->status]))
            $this->headers['Status'] = $this->status.' '.self::$status_code_text[$this->status];
        header('HTTP/1.x '.$this->headers['Status']);
		foreach($this->headers as $key => $value) header($key.': '.$value);
    }
    
    public function out()
	{
        $this->send_headers();
        echo $this->body;
    }
}

?>
