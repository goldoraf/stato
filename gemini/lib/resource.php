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
    
    public static function instantiate($name, $module = null)
    {
        if (file_exists($path = STATO_APP_PATH."/resources/base_resource.php"))
            require_once($path);
        
        $class_name = SInflection::camelize($name).'Resource';
        if (!file_exists($file_path = self::resource_file($name, $module)))
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
    
    private static function resource_file($req_resource, $module)
    {
        if ($module === null)    
            return STATO_APP_ROOT_PATH."/app/resources/{$req_resource}_resource.php";
        
        if (file_exists($path = STATO_APP_ROOT_PATH."/modules/{$module}/resources/base_resource.php"))
            require_once($path);
            
        return STATO_APP_ROOT_PATH."/modules/{$module}/resources/{$req_resource}_resource.php";
    }
}

?>
