<?php



class Stato_Mailer_Mime_Attachment extends Stato_Mailer_Mime_Part
{
    public function __construct($content = '', $filename = null, $contentType = 'application/octet-stream', $encoding = 'base64')
    {
        parent::__construct($content, $contentType, $encoding);
        $options = array();
        if ($filename !== null) $options['name'] = $filename;
        $this->setContentType($contentType, $options);
        $this->setContentDisposition($filename);
    }
    
    public function setContentDisposition($filename = null)
    {
        $value = 'attachment';
        if ($filename !== null) $value.= "; filename=\"$filename\"";
        $this->setHeader('Content-Disposition', $value, false);
    }
}
