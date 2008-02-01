<?php

class SUnknownResourceException extends Exception {}
class SHttpMethodNotImplemented extends Exception {}

class SResource
{
    protected $request;
    protected $params;
    protected $responder;
    
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
        $this->request   = $request;
        $this->params    =& $this->request->params;
        $this->responder = new SResponder($this->request->format());
        
        $method = $this->request->method();
        if (!method_exists($this, $method))
            throw new SHttpMethodNotImplemented(strtoupper($method));
        
        return $this->$method();
    }
}

?>