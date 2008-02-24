<?php

class SUnknownResourceException extends Exception {}
class SHttpMethodNotImplemented extends Exception {}

class SResource implements SIDispatchable, SIFilterable
{
    protected $request;
    protected $response;
    protected $params;
    protected $format;
    protected $mimetype;
    protected $performed = false;
    protected $accepted_formats = array('xml', 'json');
    protected $before_filters   = array();
    protected $after_filters    = array();
    
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
        
        $before_result = SFilters::process($this, 'before', $method, $this->before_filters);
        if ($before_result !== false && !$this->performed)
        {
            $result = $this->$method();
            if (!$this->performed) $this->responds($result);
        }
        SFilters::process($this, 'after', $method, $this->after_filters);
        
        return $this->response;
    }
    
    public function process_to_log($request)
    {
        return array(get_class($this), $request->method());
    }
    
    public function call_filter($method, $state)
    {
        return $this->$method();
    }
    
    protected function responds($data, $status = 200)
    {
        $serializer = SAbstractSerializer::instantiate($this->format);
        $this->responds_text($serializer->serialize($data), $status);
    }
    
    protected function responds_text($text, $status = 200)
    {
        $this->response->headers['Status'] = $status;
        $this->response->headers['Content-Type'] = (string) $this->mimetype;
        $this->response->body = $text;
        
        $this->performed = true;
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
