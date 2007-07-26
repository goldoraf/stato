<?php

class SPhpMailTransport extends SAbstractMailTransport
{
    protected $to = null;
    protected $subject = null;
    
    protected function get_headers()
    {
        $headers = parent::get_headers();
        $this->subject = $headers['Subject'];
        $this->to = $this->implode_header_value($headers['To']);
        unset($headers['Subject']);
        unset($headers['To']);
        
        return $headers;
    }
    
    protected function send_mail()
    {
        return @mail($this->to, $this->subject, $this->body, $this->headers);
    }
}

?>
