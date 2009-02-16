<?php

class Stato_Mail
{   
    public static $eol = "\r\n";
    
    public static $lineLength = 74;
    
    protected $mimeVersion = '1.0';
    
    protected $charset;
    
    protected $boundary;
    
    protected $date;
    
    protected $headers;
    
    protected $parts;
    
    public function __construct(DateTime $date = null, $charset = 'UTF-8')
    {
        if ($date === null) {
            $tz = date_default_timezone_get();
            $date = new DateTime('now', new DateTimeZone($tz));
        }
        $this->date = $date;
        $this->charset = $charset;
        $this->boundary = md5(uniqid(time()));
        $this->headers = array();
        $this->parts = array();
        $this->setDefaultHeaders();
    }
    
    public function __toString()
    {
        return $this->prepareHeaders()
        .self::$eol.self::$eol.$this->getBody();
    }
    
    public function isMultipart()
    {
        return (count($this->parts) > 1);
    }
    
    public function addTo($adress, $name = null)
    {
        $this->addRecipient('To', $adress, $name);
    }
    
    public function addCc($adress, $name = null)
    {
        $this->addRecipient('Cc', $adress, $name);
    }
    
    public function addBcc($adress, $name = null)
    {
        $this->addRecipient('Bcc', $adress, $name);
    }
    
    public function setFrom($adress, $name = null)
    {
        $this->addRecipient('From', $adress, $name);
    }
    
    public function setSubject($text)
    {
        $this->addHeader('Subject', $text);
    }
    
    public function setBody($text, $content_type = 'text/plain')
    {
        $this->addPart(array('content_type' => $content_type, 'body' => $text));
    }
    
    public function setHtmlBody($text, $content_type = 'text/html')
    {
        $this->addPart(array('content_type' => $content_type, 'body' => $text));
    }
    
    public function addPart($params)
    {
        $this->parts[] = new Stato_MailPart($params);
    }
    
    public function addAttachment($params)
    {
        $this->parts[] = new Stato_MailAttachment($params);
    }
    
    public function addHeader($name, $value, $encode = true)
    {
        if ($encode) $value = $this->encodeHeader($value);
        if (isset($this->headers[$name])) $this->headers[$name][] = $value;
        else $this->headers[$name] = array($value);
    }
    
    public function getHeaders()
    {
        if (!$this->isMultipart()) {
            return array_merge($this->headers, $this->getFirstPart()->getHeaders());
        } else {
            $p = new Stato_MailPart(array('content_type' => 'multipart/mixed', 'content_disposition' => null,
                                          'boundary' => $this->boundary, 'body' => ''));
            return array_merge($this->headers, $p->getHeaders());
        }
    }
    
    public function getBody()
    {
        if (!$this->isMultipart()) {
            return $this->getFirstPart()->getContent();
        }
        
        $body = 'This is a multi-part message in MIME format.';
        
        foreach ($this->parts as $part) {
            $body.= $this->boundaryLine()
                   .$this->prepareHeaders($part->getHeaders())
                   .self::$eol.self::$eol.$part->getContent();
        }
        $body.= $this->boundaryEnd();
        
        return $body;
    }
    
    public function prepareHeaders($headers = null)
    {
        if ($headers === null) $headers = $this->getHeaders();
        $h = array();
        foreach ($headers as $k => $v)
            $h[] = "$k: ".$this->implodeHeaderValue($v);
        
        return implode(self::$eol, $h);
    }
    
    public function setBoundary($boundary)
    {
        $this->boundary = $boundary;
    }
    
    private function getFirstPart()
    {
        if (empty($this->parts)) {
            throw new Exception('No body specified');
        }
        return $this->parts[0];
    }
    
    private function implodeHeaderValue($value)
    {
        if (!is_array($value)) return $value;
        if (count($value) == 1) return array_pop($value);
        return implode(', ', $value);
    }
    
    private function addRecipient($header, $adress, $name)
    {
        $adress = strtr($adress, "\r\n\t", '???');
        if ($name !== null) $adress = $this->encodeHeader($name)." <$adress>";
        $this->addHeader($header, $adress, false);
    }
    
    private function encodeHeader($text)
    {
        if (Stato_Mime::isPrintable($text)) return $text;
        $quoted = Stato_Mime::encode(str_replace("\n", '', $text), Stato_Mime::QUOTED_PRINTABLE);
        $quoted = str_replace(array('?', ' ', '_'), array('=3F', '=20', '=5F'), $quoted);
        return "=?{$this->charset}?Q?{$quoted}?=";
    }
    
    private function setDefaultHeaders()
    {
        $this->headers['Date'] = $this->date->format(DateTime::RFC822);
        $this->headers['MIME-Version'] = $this->mimeVersion;
    }
    
    private function boundaryLine()
    {
        return self::$eol.'--'.$this->boundary.self::$eol;
    }
    
    private function boundaryEnd()
    {
        return self::$eol.'--'.$this->boundary.'--';
    }
}

interface Stato_IMailTransport
{
    public function send(Stato_Mail $mail);
}
