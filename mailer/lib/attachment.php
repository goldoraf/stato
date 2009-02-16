<?php

class Stato_MailAttachment extends Stato_MailPart
{
    protected $defaultParams = array(
        'content_type' => 'application/octet-stream',
        'content_disposition' => 'attachment', 
        'encoding' => 'base64', 
        'charset' => null
    );
    
    public function __construct($params)
    {
        $params = array_merge($this->defaultParams, $params);
        parent::__construct($params);
    }
}
