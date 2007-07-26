<?php

abstract class SAbstractMailTransport
{
    protected $mail = null;
    protected $headers = null;
    protected $body = null;
    protected $mime_version = '1.0';
    protected $boundary = null;
    
    public function __construct()
    {
    
    }
    
    public function send($mail)
    {
        $this->mail = $mail;
        $this->headers = $this->prepare_headers($this->get_headers());
        
        if (!$this->mail->is_multipart())
        {
            $body = array_pop($this->mail->parts);
            $this->headers.= SMailer::$eol.$this->prepare_headers($body->headers());
            $this->body = $body->content();
        }
        else
        {
            $this->boundary = md5(uniqid(time()));
            
            $p = new SPart(array('content_type' => 'multipart/mixed', 'content_disposition' => null,
                                 'body' => 'This part of the E-mail should never be seen. '
                                          .'If you are reading this, consider upgrading your e-mail '
                                          .'client to a MIME-compatible client.'));
            $this->body = $p->content();
            $this->headers.= SMailer::$eol.$this->prepare_headers($p->headers($this->boundary));
            
            foreach ($this->mail->parts as $part)
            {
                $this->body.= $this->boundary_line()
                              .$this->prepare_headers($part->headers())
                              .SMailer::$eol.$part->content();
            }
            $this->body.= $this->boundary_end();
        }
        
        return $this->send_mail();
    }
    
    protected function get_headers()
    {
        $headers = $this->mail->headers;
        $headers['Date'] = $this->rfc_date();
        //$headers['Message-ID'] = ...
        $headers['MIME-Version'] = $this->mime_version;
        
        return $headers;
    }
    
    protected function prepare_headers($headers)
    {
        $h = array();
        foreach ($headers as $k => $v)
            $h[] = "$k: ".$this->implode_header_value($v);
        
        return implode(SMailer::$eol, $h);
    }
    
    protected function implode_header_value($value)
    {
        if (!is_array($value)) return $value;
        if (count($value) == 1) return array_pop($value);
        return implode(', ', $value);
    }
    
    abstract protected function send_mail();
    
    // taken from phpmailer
    private function rfc_date()
    {
        $tz = date("Z");
        $tzs = ($tz < 0) ? "-" : "+";
        $tz = abs($tz);
        $tz = ($tz/3600)*100 + ($tz%3600)/60;
        $result = sprintf("%s %s%04d", date("D, j M Y H:i:s"), $tzs, $tz);

        return $result;
    }
    
    private function boundary_line()
    {
        return SMailer::$eol.'--'.$this->boundary.SMailer::$eol;
    }
    
    private function boundary_end()
    {
        return SMailer::$eol.'--'.$this->boundary.'--';
    }
}

?>
