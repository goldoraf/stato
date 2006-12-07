<?php

class SWebServiceApi
{
    protected $api_methods = array();
    protected $api_methods_public_names = array();
    
    public function add_api_method($name, $expects, $returns)
    {
        if (!is_array($expects))
            throw new SException('Expected an array for $expects option');
        
        $public_name = SInflection::camelize($name);
        $this->api_methods[$name] = new SWebServiceMethod($name, $public_name, $expects, $returns);
        $this->api_methods_public_names[$public_name] = $name;
    }
    
    public function has_api_method($name)
    {
        return isset($this->api_methods[$name]);
    }
    
    public function has_public_api_method($public_name)
    {
        return isset($this->api_methods_public_names[$public_name]);
    }
    
    public function api_method_name($public_name)
    {
        return $this->api_methods_public_names[$public_name];
    }
    
    public function public_api_method_instance($public_name)
    {
        return $this->api_method_instance($this->api_method_name($public_name));
    }
    
    public function api_method_instance($name)
    {
        return $this->api_methods[$name];
    }
}

class SWebServiceMethod
{
    private $name;
    private $public_name;
    private $expects;
    private $returns;
    
    public function __construct($name, $public_name, $expects, $returns)
    {
        $this->name = $name;
        $this->public_name = $public_name;
        $this->expects = $expects;
        $this->returns = $returns;
    }
    
    public function param_names()
    {
        return array_keys($this->expects);
    }
}

?>
