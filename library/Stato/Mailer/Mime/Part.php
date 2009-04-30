<?php

namespace Stato\Mailer\Mime;

class Part extends Entity
{
    public function __construct($content = '', $contentType = 'text/plain', $encoding = '8bit', $charset = 'UTF-8')
    {
        parent::__construct();
        $this->content = $content;
        $this->charset = $charset;
        $this->setContentType($contentType, array('charset' => $charset));
        $this->setEncoding($encoding);
    }
    
    public function __toString()
    {
        return $this->getAllHeaderLines()
        .$this->eol.$this->eol.$this->getContent();
    }
    
    public function getContent()
    {
        return Mime::encode($this->content, $this->getEncoding(), $this->lineLength, $this->eol);
    }
    
    public function setContent($content, $contentType = 'text/plain')
    {
        $this->content = $content;
        $this->setContentType($contentType);
    }
    
    public function setContentType($contentType, $options = array())
    {
        $value = $contentType;
        foreach ($options as $k => $v) {
            if ($v !== null) $value.= "; $k=\"$v\"";
        }
        $this->setHeader('Content-Type', $value, false);
    }
    
    public function setEncoding($encoding)
    {
        $this->setHeader('Content-Transfer-Encoding', $encoding);
    }
    
    public function getEncoding()
    {
        return $this->getHeader('Content-Transfer-Encoding');
    }
}
