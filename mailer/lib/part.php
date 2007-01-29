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
        if (is_resource($this->body))
            return $this->encode_stream();
        else
            return $this->encode();
    }
    
    public function encode()
    {
        switch ($this->encoding)
        {
            case 'base64':
                return $this->encode_base_64($this->body);
            case 'quoted-printable':
                return $this->encode_quoted_printable($this->body);
            default:
                return wordwrap($this->body, SMailer::$line_length);
        }
    }
    
    public function encode_stream()
    {
        if ($this->encoding == 'base64') $filter = 'convert.base64-encode';
        elseif ($this->encoding == 'quoted-printable') $filter = 'convert.quoted-printable-encode';
        else throw new Exception("No stream filter available for {$this->encoding} encoding");
        
        $this->append_stream_filter($this->body, $filter);
        return stream_get_contents($this->body);
    }
    
    public function headers($boundary = null)
    {
        $headers = array();
        
        $headers['Content-Type'] = $this->content_type;
        if ($this->charset !== null && $boundary === null) 
            $headers['Content-Type'].= '; charset="'.$this->charset.'"';
        if ($boundary !== null)
            $headers['Content-Type'].= '; boundary="'.$boundary.'"';
            
        $headers['Content-Transfer-Encoding'] = $this->encoding;
        
        if ($this->content_disposition !== null)
        {
            $headers['Content-Disposition'] = $this->content_disposition;
            if ($this->filename !== null)
                $headers['Content-Disposition'].= '; filename="'.$this->filename.'"';
        }
        
        return $headers;
    }
    
    protected function encode_base_64($str)
    {
        return rtrim(chunk_split(base64_encode($str), SMailer::$line_length, SMailer::$eol));
    }
    
    protected function encode_quoted_printable($str)
    {
        $fp = fopen('php://temp/', 'r+');
        $this->append_stream_filter($fp, 'convert.quoted-printable-encode');
        fputs($fp, $str);
        rewind($fp);
        return stream_get_contents($fp);
    }
    
    protected function append_stream_filter($stream, $filter_name)
    {
        $params = array('line-length' => SMailer::$line_length, 'line-break-chars' => SMailer::$eol);
        stream_filter_append($stream, $filter_name, STREAM_FILTER_READ, $params);
    }
}

?>
