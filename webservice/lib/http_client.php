<?php

class SHttpClient
{   
    public $timeout  = 10;
    
    private $uri     = null;
    private $headers = array();
    
    public function __construct($uri, $headers = array())
    {
        $this->uri = $uri;
        $this->headers = $headers;
    }
    
    public function get($redirectMax = 5)
    {
        $ch = $this->connect();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, $redirectMax);
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
        return $ch;
    }
    
    private function execute($ch)
    {
        if ($this->isSsl())
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // win2k hack
        }
        
        $reponse = new SHttpResponse();
        $response->body = curl_exec($ch);
        
        if (curl_errno($ch) != 0)
            throw new SException("cURL error : ".curl_error($ch));
        
        $response->code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response->headers['Content-Type']   = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $response->headers['Content_Length'] = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        
        curl_close($ch);
        
        return $response;
    }
    
    private function isSsl()
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
