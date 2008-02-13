<?php

class SWebServiceException extends Exception {}
class SWebServiceFault extends Exception {}
class SWebServiceCastingException extends Exception
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
    
    private static $base_types = array('integer', 'float', 'string', 'datetime', 'boolean', 'base64');
    
    public function __construct()
    {
        $api_class = str_replace('Service', '', get_class($this)).'Api';
        if (!class_exists($api_class, false))
            throw new SWebServiceException("$api_class class not found.");
        
        $this->api = new $api_class();
        
        SDependencies::require_models($this->models);
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
    
    public function is_struct_type($type)
    {
        if (in_array($type, self::$base_types)) return false;
        
        try {
            $ref = new ReflectionClass($type);
            return $ref->isSubclassOf(new ReflectionClass('SWebServiceStruct'));
        } catch (Exception $e) {
            return false;
        }
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
            
            if (count($type) == 1) // we're waiting for an array of arguments of the same type
            {
                foreach ($value->value as $v)
                    $casted_value[] = $this->cast($v, $type[0]);
            }
            else
            {
                foreach ($type as $k => $t)
                    $casted_value[$k] = $this->cast(array_shift($value->value), $t);
            }
            
            return $casted_value;
        }
        
        if ($this->is_struct_type($type))
        {
            if (!is_array($value->value))
                throw new SWebServiceCastingException();
            
            $struct = new $type();
            foreach ($value->value as $k => $v)
                if ($struct->member_exists($k)) 
                    $struct->$k = $this->cast($v, $struct->member_type($k));
                
            return $struct;
        }
        
        if ($value->type != $type)
            throw new SWebServiceCastingException();
            
        return $value->to_php();
    }
}

?>
