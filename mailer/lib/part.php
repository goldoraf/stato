<?php

class Stato_MailPart
{
    protected $body = null;
    
    protected $charset = 'utf-8';
    
    protected $content_disposition = 'inline';
    
    protected $content_type = null;
    
    protected $boundary = null;
    
    protected $filename = null;
    
    protected $encoding = '8bit';
    
    public function __construct($params)
    {
        if (!isset($params['body']) || !isset($params['content_type']))
            throw new Exception('body and content_type are required parameters.');
        
        $ref = new ReflectionClass(get_class($this));
        foreach ($params as $k => $v)
            if ($ref->hasProperty($k)) $this->$k = $v;
    }
    
    public function getContent()
    {
        if (!is_resource($this->body) && $this->encoding == '8bit' 
            /*&& preg_match('/format=flowed/', $this->content_type)*/)
            return $this->body;
            
        return Stato_Mime::encode($this->body, $this->encoding);
    }
    
    public function getHeaders()
    {
        $headers = array();
        
        $headers['Content-Type'] = $this->content_type;
        if ($this->charset !== null && $this->boundary === null && !preg_match('/charset=/', $this->content_type)) 
            $headers['Content-Type'].= '; charset="'.$this->charset.'"';
        if ($this->boundary !== null)
            $headers['Content-Type'].= '; boundary="'.$this->boundary.'"';
            
        $headers['Content-Transfer-Encoding'] = $this->encoding;
        
        if ($this->content_disposition !== null)
        {
            $headers['Content-Disposition'] = $this->content_disposition;
            if ($this->filename !== null)
                $headers['Content-Disposition'].= '; filename="'.$this->filename.'"';
        }
        
        return $headers;
    }
}
