<?php

class SAttachment extends SPart
{
    public function __construct($params)
    {
        $params = array_merge(array('content_type' => 'application/octet-stream',
            'content_disposition' => 'attachment', 'encoding' => 'base64'), $params);
        
        parent::__construct($params);
    }
}

?>
