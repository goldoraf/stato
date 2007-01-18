class <?php echo $controller_class_name; ?> extends ApplicationController
{
    protected $layout = 'ws_test';
    protected $endpoint = 'api/xmlrpc';
    
    public function index()
    {
        $this->services = $this->api_classes();
    }
    
    public function set_params()
    {
        $this->require_api_classes();
        $this->service_name = $this->params['service'];
        $api_class = $this->service_name.'Api';
        $this->api = new $api_class();
        $this->method = $this->api->api_method_instance($this->params['method']);
    }
    
    public function invoke()
    {
        $this->require_api_classes();
        $client = new SXmlRpcClient('http://127.0.0.1'.$this->request->relative_url_root().$this->endpoint);
        $this->service = $this->params['service'];
        $api_class = $this->service.'Api';
        $this->api = new $api_class();
        $this->method = $this->api->public_api_method_instance($this->params['method']);
        $this->method_params 
            = $this->cast_expects($this->params['method_params'], $this->method);
        
        $method = $this->service.'.'.$this->method->public_name;
        $request = new SXmlRpcRequest($method, $this->method_params);
        $this->request_xml = $request->to_xml();
        $this->response_xml = $client->send_request($method, $this->method_params);
        $this->return_value = $client->decode_response($this->response_xml);
    }
    
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
            if (!is_array($value))
                throw new SException();
                
            $casted_value = array();
            
            if (count($type) == 1) // we're waiting for an array of arguments of the same type
                foreach ($value as $v) $casted_value[] = $this->cast($v, $type[0]);
            else
                foreach ($type as $k => $t) $casted_value[$k] = $this->cast(array_shift($value), $t);
            
            return $casted_value;
        }
        
        if (SWebService::is_struct_type($type))
        {
            if (!is_array($value))
                throw new SException();
            
            $struct = new $type();
            foreach ($value as $k => $v)
                $struct->$k = $this->cast($v, $struct->member_type($k));
                
            return $struct;
        }
            
        switch ($type)
        {
            case 'integer':
                return (integer) $value;
            case 'float':
                return (float) $value;
            case 'boolean':
                return $value == 'true';
            case 'string':
                return $value;
            case 'datetime':
                return SDateTime::from_array($value);
            case 'base64':
                return new SBase64($value);
        }
    }
    
    private function api_classes()
    {
        $before_classes = get_declared_classes();
        $this->require_api_classes();
        $new_classes = array_diff(get_declared_classes(), $before_classes);
        $api_classes = array();
        foreach ($new_classes as $c)
        {
            $ref = new ReflectionClass($c);
            if ($ref->getParentClass()->getName() == 'SWebServiceApi')
            {
                $service_name = str_replace('api', '', strtolower($c));
                $api_classes[$service_name] = new $c;
            }
        }
        return $api_classes;
    }
    
    private function require_api_classes()
    {
        $it = new DirectoryIterator(STATO_APP_PATH.'/apis');
        foreach ($it as $file)
        {
            if ($file->isDot() || $file->getFilename() == '.svn') continue;
            if ($file->isFile()) require(STATO_APP_PATH.'/apis/'.$file->getFilename());
        }
    }
}

function method_parameter_label($name, $type)
{
    return (is_int($name)) ? "Parameter $name" : ucfirst($name);
}

function method_parameter_input_fields($method, $type, $field_name_base, $idx, $was_structured = false)
{
    if (is_array($type) && count($type) == 1)
        return content_tag('em', "Typed array input fields not supported yet");
    
    if (SWebService::is_struct_type($type) || is_array($type))
    {
        if ($was_structured)
            return content_tag('em', "Nested structural types not supported yet");
            
        $params = '';
        if (is_array($type)) $members = $type;
        else
        {
            $type = new $type();
            $members = $type->members_list();
        }
        foreach ($members as $name => $type)
        {
            $label = method_parameter_label($name, $type);
            $content = method_parameter_input_fields($method, $type, "{$field_name_base}[{$idx}][{$name}]", $idx, true);
            $params.= content_tag('li', $label.' '.$content);
        }
        return content_tag('ul', $params);
    }
    
    // If the data source was structured previously we already have the index set          
    if (!$was_structured) $field_name_base = "{$field_name_base}[{$idx}]";
    
    switch ($type)
    {
        case 'integer':
            return text_field_tag($field_name_base);
        case 'float':
            return text_field_tag($field_name_base);
        case 'string':
            return text_field_tag($field_name_base);
        case 'base64':
            return text_area_tag($field_name_base);
        case 'boolean':
            return radio_button_tag($field_name_base, 'true').' True'
            .radio_button_tag($field_name_base, 'false').' False';
        case 'datetime':
            return select_date_time(SDateTime::now(), array('prefix' => $field_name_base));
    }
}
