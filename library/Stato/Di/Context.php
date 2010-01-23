<?php



class Stato_Di_Context
{
    private $classes;
    private $instances;
    
    public function __construct()
    {
        $this->classes = array();
        $this->instances = array();
    }
    
    public function register($argumentName, $className, $args = array())
    {
        $this->classes[$argumentName] = array($className, $args);
    }
    
    public function has($argumentName)
    {
        return array_key_exists($argumentName, $this->classes);
    }
    
    public function get($argumentName)
    {
        if (!array_key_exists($argumentName, $this->instances)) {
            // throw exception si pas trouvé
            list($className, $args) = $this->classes[$argumentName];
            $this->instances[$argumentName] = $this->instantiate($className, $args);
        }
            
        return $this->instances[$argumentName];
    }
    
    private function instantiate($className, $args)
    {
        $ref = new ReflectionClass($className);
        return is_null($ref->getConstructor()) ? $ref->newInstance() : $ref->newInstanceArgs($args);
    }
}