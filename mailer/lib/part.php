<?php

class SPart
{
    protected $body = null;
    protected $charset = 'utf-8';
    protected $content_disposition = 'inline';
    protected $content_type = null;
    protected $filename = null;
    protected $encoding = '8bit';
    
    public function __construct($params)
    {
        if (!isset($params['body']) || !isset($params['content_type']))
            throw new Exception('Body and content-type are required parameters.');
        
        $ref = new ReflectionClass(get_class($this));
        foreach ($params as $k => $v)
            if ($ref->hasProperty($k)) $this->$k = $v;
    }
    
    public function content()
    {
        if ($this->encoding == 'base64')
            return $this->encode_base_64($this->body);
        else
            return wordwrap($this->body, SMailer::$line_length);
    }
    
    public function headers($boundary = null)
    {
        $headers = array();
        
        $headers['Content-Type'] = $this->content_type.'; charset="'.$this->charset.'"';
        if ($boundary !== null)
            $headers['Content-Type'].= ';'.SMailer::$eol.' boundary="'.$boundary.'"';
            
        $headers['Content-Transfert-Encoding'] = $this->encoding;
        
        $headers['Content-Disposition'] = $this->content_disposition;
        if ($this->filename !== null)
            $headers['Content-Disposition'].= '; filename="'.$this->filename.'"';
        
        return $headers;
    }
    
    protected function encode_base_64($str)
    {
        return rtrim(chunk_split(base64_encode($str), SMailer::$line_length, SMailer::$eol));
    }
}

?>
