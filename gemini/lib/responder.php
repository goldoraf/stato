<?php

class SResponder
{
    protected $format;
    protected $mimetype;
    
    public function __construct($format)
    {
        $this->format = $format;
        $this->mimetype = SMimeType::lookup($this->format);
    }
    
    public function element($elem)
    {
        return $this->render($elem, '200 OK');
    }
    
    public function collection($collec)
    {
        return $this->render($collec, '200 OK');
    }
    
    public function error($status_code, $errors = array())
    {
        return $this->render($errors, $status_code.' '.SResponse::$status_code_text[$status_code]);
    }
    
    protected function render($data, $status)
    {
        $serializer_class = "S{$this->format}Serializer";
        $serializer = new $serializer_class();
        
        $response = new SResponse();
        $response->headers['Status'] = $status;
        $response->headers['Content-Type'] = (string) $this->mimetype;
        $response->body = $serializer->serialize($data);
        
        return $response;
    }
}    

?>