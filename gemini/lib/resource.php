<?php

class SUnknownResourceException extends Exception {}
class SHttpMethodNotImplemented extends Exception {}

class SResource
{
    protected $request;
    protected $response;
    protected $params;
    protected $format;
    protected $mimetype;
    protected $accepted_formats = array('xml', 'json');
    
    public static function instantiate($name)
    {
        $class_name = SInflection::camelize($name).'Resource';
        $file_path = STATO_APP_PATH."/resources/{$name}_resource.php";
        if (!file_exists($file_path))
            throw SUnknownResourceException("$class_name not found !");
        require_once($file_path);
        return new $class_name();
    }
    
    public function dispatch(SRequest $request, SResponse $response)
    {
        $this->request  = $request;
        $this->response = $response;
        $this->params   =& $this->request->params;
        $this->format   = $this->request->format();
        $this->mimetype = SMimeType::lookup($this->format);
        
        $method = $this->request->method();
        if (!method_exists($this, $method))
            throw new SHttpMethodNotImplemented(strtoupper($method));
        
        $result = $this->$method();
        return $this->responds($result);
    }
    
    public function process_to_log($request)
    {
        return array(get_class($this), $request->method());
    }
    
    protected function responds($data, $status = 200)
    {
        $serializer = SAbstractSerializer::instantiate($this->format);
        
        $this->response->headers['Status'] = $status;
        $this->response->headers['Content-Type'] = (string) $this->mimetype;
        $this->response->body = $serializer->serialize($data);
        
        return $this->response;
    }
}

?>