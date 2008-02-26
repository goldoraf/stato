<?php

class SUnknownResourceException extends Exception {}
class SHttpMethodNotImplemented extends Exception
{
    private $allowed_methods;
    
    public function __construct($resource, $requested_method)
    {
        $ref = new ReflectionObject($resource);
        $this->allowed_methods = array();
        foreach ($ref->getMethods() as $method)
        {
            if ($method->isPublic() && !$method->isConstructor()
                && $method->getDeclaringClass()->getName() != 'SResource'
                && in_array($method->getName(), SRequest::$accepted_http_methods))
                $this->allowed_methods[] = strtoupper($method->getName());
        }
        parent::__construct(strtoupper($requested_method).' not implemented on this resource');
    }
    
    public function handle_response($response)
    {
        $response->headers['Allow'] = implode(', ', $this->allowed_methods);
        return $response;
    }
}

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
            throw new SHttpMethodNotImplemented($this, $method);
        
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
    
    protected function add_before_filter($filter, $options = array())
    {
        $this->before_filters[$filter] = $options;
    }
    
    protected function add_after_filter($filter, $options = array())
    {
        $this->after_filters[$filter] = $options;
    }
    
    protected function responds($data, $status = 200)
    {
        $serializer = SAbstractSerializer::instantiate($this->format);
        $this->responds_text($serializer->serialize($data), $status);
    }
    
    protected function responds_created($object, $status = 201)
    {
        $this->responds($object, $status);
    }
    
    protected function responds_deleted($status = 204)
    {
        $this->responds_nothing($status);   
    }
    
    protected function responds_nothing($status = 200)
    {
        $this->responds_text(' ', $status);
    }
    
    protected function responds_error($status = 400)
    {
        $this->responds_nothing($status);
    }
    
    protected function responds_detailed_error($err_object, $status = 400)
    {
        $this->responds($err_object, $status);
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
