<?php

class SUnknownResourceException extends Exception {}
class SHttpMethodNotImplemented extends Exception {}

class SResource
{
    protected $request;
    protected $params;
    protected $format;
    protected $mimetype;
    protected $accepted_formats = array('xml', 'json');
    
    public static function instanciate($name)
    {
        $class_name = SInflection::camelize($name).'Resource';
        $file_path = STATO_APP_PATH."/resources/{$name}_resource.php";
        if (!file_exists($file_path))
            throw SUnknownResourceException("$class_name not found !");
        require_once($file_path);
        return new $class_name();
    }
    
    public function dispatch(SRequest $request)
    {
        $this->request  = $request;
        $this->params   =& $this->request->params;
        $this->format   = $this->request->format();
        $this->mimetype = SMimeType::lookup($this->format);
        
        $method = $this->request->method();
        if (!method_exists($this, $method))
            throw new SHttpMethodNotImplemented(strtoupper($method));
        
        $result = $this->$method();
        return $this->responds($result);
    }
    
    protected function responds($data, $status = 200)
    {
        $serializer = $this->instantiate_serializer();
        
        $response = new SResponse();
        $response->headers['Status'] = $status;
        $response->headers['Content-Type'] = (string) $this->mimetype;
        $response->body = $serializer->serialize($data);
        
        return $response;
    }
    
    protected function instantiate_serializer()
    {
        $serializer_class = "S{$this->format}Serializer";
        if (!class_exists($serializer_class, false))
            throw new Exception();
        
        return new $serializer_class();
    }
}

?>