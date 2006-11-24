<?php

class SMail
{   
    public $headers = array();
    public $parts   = array();
    
    public function __construct()
    {
        
    }
    
    public function is_multipart()
    {
        return (count($this->parts) > 1);
    }
    
    public function add_to($adress, $name = null)
    {
        $this->add_recipient('To', $adress, $name);
    }
    
    public function add_cc($adress, $name = null)
    {
        $this->add_recipient('Cc', $adress, $name);
    }
    
    public function add_bcc($adress, $name = null)
    {
        $this->add_recipient('Bcc', $adress, $name);
    }
    
    public function set_from($adress, $name = null)
    {
        $this->add_recipient('From', $adress, $name);
    }
    
    public function set_subject($text)
    {
        $this->headers['Subject'] = $this->encode_header($text);
    }
    
    public function set_body($text)
    {
        $this->parts[] = new SPart(array('content_type' => 'text/plain', 'body' => $text));
    }
    
    public function set_html_body($text)
    {
        $this->parts[] = new SPart(array('content_type' => 'text/html', 'body' => $text));
    }
    
    public function add_part($params)
    {
        $this->parts[] = new SPart($params);
    }
    
    public function add_attachment($params)
    {
        $this->parts[] = new SAttachment($params);
    }
    
    private function add_recipient($header, $adress, $name)
    {
        $adress = strtr($adress, "\r\n\t", '???');
        if ($name !== null) $adress = $this->encode_header($name)." <$adress>";
        $this->store_header($header, $adress);
    }
    
    private function store_header($name, $value)
    {
        $value = $this->encode_header($value);
        if (isset($this->headers[$name])) $this->headers[$name][] = $value;
        else $this->headers[$name] = array($value);
    }
    
    private function encode_header($text)
    {
        return str_replace("\n", '', $text);
    }
}

?>
