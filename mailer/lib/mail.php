<?php

class SMail
{
    public $to = array();
    public $cc = array();
    public $bcc = array();
    public $from = null;
    public $reply_to = null;
    public $subject = null;
    public $body = null;
    
    public function __construct()
    {
        
    }
    
    public function headers()
    {
        $headers = array();
        
        if (count($this->to) == 0 && count($this->cc) == 0)
            $headers['To'] = 'undisclosed-recipients:';
        if (count($this->to) > 0)
            $headers['To'] = $this->recipients_list($this->to);
        if (count($this->cc) > 0)
            $headers['Cc'] = $this->recipients_list($this->cc);
        if (count($this->bcc) > 0)
            $headers['Bcc'] = $this->recipients_list($this->bcc);
            
        $headers['From'] = $this->recipients_list(array($this->from));
        $headers['Date'] = $this->rfc_date();
        $headers['Subject'] = $this->encode_header($this->subject);
        
        return $headers;
    }
    
    private function encode_header($text)
    {
        return str_replace("\n", '', $text);
    }
    
    private function recipients_list($addr)
    {
        foreach ($addr as $k => $v) 
            if (is_array($v)) $addr[$k] = $this->encode_header($v[0]).' <'.$v[1].'>';
                
        return implode(', ', $addr);
    }
    
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
    
    private function wrap_text($str)
    {
    
    }
}

?>
