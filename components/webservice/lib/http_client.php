<?php

class SHttpClientException extends Exception {}

class SHttpClient
{   
    public $timeout  = 10;
    
    private $uri     = null;
    private $headers = array();
    private $credentials = null;
    
    public function __construct($uri, $headers = array(), $credentials = null)
    {
        $this->uri = $uri;
        $this->headers = $headers;
        $this->credentials = $credentials;
    }
    
    public function get($redirect_max = 5)
    {
        $ch = $this->connect();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, $redirect_max);
        return $this->execute($ch);
    }
    
    public function post($data = '')
    {
        $ch = $this->connect();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        return $this->execute($ch);        
    }
    
    private function connect()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        if (!empty($this->headers))
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        if ($this->credentials !== null)
            curl_setopt($ch, CURLOPT_USERPWD, $this->credentials);
        return $ch;
    }
    
    private function execute($ch)
    {
        if ($this->is_ssl())
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // win2k hack
        }
        
        $response = new SHttpResponse();
        $response->body = curl_exec($ch);
        
        if (curl_errno($ch) != 0)
            throw new SHttpClientException("cURL error : ".curl_error($ch));
        
        $response->code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response->headers['Content-Type']   = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $response->headers['Content_Length'] = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        
        curl_close($ch);
        
        return $response;
    }
    
    private function is_ssl()
    {
        list($host, $uri) = explode('://', $this->uri);
        return $host == 'https';
    }
}

class SHttpResponse
{
    public $code    = null;
    public $headers = array();
    public $body    = null;
}

?>