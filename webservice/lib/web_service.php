<?php

class SWebServiceException extends SException {}
class SWebServiceFault extends SException {}
class SWebServiceCastingException extends SException
{
    protected $message = 'Calling parameters do not match API definition';
}

class SWebService
{
    protected $models  = array();
    
    protected $request = null;
    protected $method  = null;
    protected $params  = null;
    protected $api     = null;
    
    public function __construct()
    {
        $api_class = str_replace('Service', '', get_class($this)).'Api';
        if (!class_exists($api_class))
            throw new SWebServiceException("$api_class class not found.");
        
        $this->api = new $api_class();
        
        SDependencies::require_dependencies('models', $this->models, get_class($this));
    }
    
    public function invoke($request)
    {
        if (!$this->api->has_public_api_method($request->method))
            throw new SWebServiceException("Unknown {$request->service}.{$request->method} method.");
        
        $this->method  = $this->api->api_method_name($request->method);
        $this->request = $request;
        $this->params  = $this->cast_expects($this->request->params, $this->api->api_method_instance($this->method));
        
        $this->before_invocation();
        $response = $this->{$this->method}();
        $this->after_invocation();
        
        return $response;
    }
    
    protected function before_invocation() {}
    
    protected function after_invocation() {}
    
    private function cast_expects($params, $api_method)
    {
        if ($api_method->expects === null) return array();
        $casted_params = array();
        foreach ($api_method->expects as $k => $type)
            $casted_params[$k] = $this->cast(array_shift($params), $type);
            
        return $casted_params;
    }
    
    private function cast($value, $type)
    {
        if (is_array($type))
        {
            if ($value->type != 'array')
                throw new SWebServiceCastingException();
                
            $casted_value = array();
            foreach ($value->value as $v)
                $casted_value[] = $this->cast($v, $type[0]);
            
            return $casted_value;
        }
        
        if ($this->is_struct_type($type))
        {
            if (!is_array($value->value))
                throw new SWebServiceCastingException();
            
            $struct = new $type();
            foreach ($value->value as $k => $v)
                $struct->$k = $this->cast($v, $struct->member_type($k));
                
            return $struct;
        }
        
        if ($value->type != $type)
            throw new SWebServiceCastingException();
            
        return $value->to_php();
    }
    
    private function is_struct_type($type)
    {
        try {
            $ref = new ReflectionClass($type);
            return $ref->getParentClass()->getName() == 'SWebServiceStruct';
        } catch (Exception $e) {
            return false;
        }
    }
}

?>
