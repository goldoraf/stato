<?php

class Stato_MailException extends Exception {}

/**
 * Class representing an email message
 * 
 * 
 * <code>
 * $mail = new Stato_Mail();
 * $mail->addTo('foo@bar.net');
 * $mail->setBody('hello world');
 * $mail->send(new Stato_SendmailTransport());
 * </code>
 *
 * @package Stato
 * @subpackage mailer
 */
class Stato_Mail
{   
    public static $eol = "\n";
    
    public static $lineLength = 72;
    
    protected $mimeVersion = '1.0';
    
    protected $charset;
    
    protected $boundary;
    
    protected $date;
    
    protected $from;
    
    protected $recipients;
    
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
        $this->recipients = array();
        $this->headers = array();
        $this->parts = array();
        $this->setDefaultHeaders();
    }
    
    public function __toString()
    {
        return $this->prepareHeaders()
        .self::$eol.self::$eol.$this->getBody();
    }
    
    public function send(Stato_IMailTransport $transport)
    {
        return $transport->send($this);
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
        $this->from = $adress;
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
    
    public function getTo()
    {
        if (!array_key_exists('To', $this->headers))
            throw new Stato_MailException('To: recipient is not specified');
        
        return $this->getHeaderValue('To');
    }
    
    public function getFrom()
    {
        return $this->getHeaderValue('From');
    }
    
    public function getCc()
    {
        return $this->getHeaderValue('Cc');
    }
    
    public function getBcc()
    {
        return $this->getHeaderValue('Bcc');
    }
    
    public function getSubject()
    {
        return $this->getHeaderValue('Subject');
    }
    
    public function getReturnPath()
    {
        return $this->from;
    }
    
    public function getRecipients()
    {
        return $this->recipients;
    }
    
    public function getHeaders($exclude = array())
    {
        if (!$this->isMultipart()) {
            $headers = array_merge($this->headers, $this->getFirstPart()->getHeaders());
        } else {
            $p = new Stato_MailPart(array('content_type' => 'multipart/mixed', 'content_disposition' => null,
                                          'boundary' => $this->boundary, 'body' => ''));
            $headers = array_merge($this->headers, $p->getHeaders());
        }
        foreach ($exclude as $key) {
            if (array_key_exists($key, $headers)) unset($headers[$key]);
        }
        return $headers;
    }
    
    public function getHeaderValue($key)
    {
        if (!array_key_exists($key, $this->headers)) return '';
        return $this->implodeHeaderValue($this->headers[$key]);
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
            throw new Stato_MailException('No body specified');
        }
        return $this->parts[0];
    }
    
    private function implodeHeaderValue($value)
    {
        if (!is_array($value)) return $value;
        if (count($value) == 1) return array_pop($value);
        return implode(', ', $value);
    }
    
    private function addRecipient($header, $address, $name)
    {
        $address = strtr($address, "\r\n\t", '???');
        if (!in_array($address, $this->recipients)) $this->recipients[] = $address;
        if ($name !== null) $address = $this->encodeHeader($name)." <$address>";
        $this->addHeader($header, $address, false);
    }
    
    private function encodeHeader($text)
    {
        return mb_encode_mimeheader($text, $this->charset, 'Q', self::$eol);
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
