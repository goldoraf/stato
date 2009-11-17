<?php

namespace Stato\Di;

class Injector
{
    private $context;
    
    public function __construct(Context $context)
    {
        $this->context = $context;
    }
    
    public function build($className, $additionalArgs = array())
    {
        $args = $this->prepareArgs($className, $additionalArgs);
        $ref = new \ReflectionClass($className);
        return $ref->newInstanceArgs($args);
    }
    
    private function prepareArgs($className, array $additionalArgs)
    {
        $args = array();
        $ref = new \ReflectionClass($className);
        $params = $ref->getConstructor()->getParameters();
        
        foreach ($params as $param) {
            $argName = $param->getName();
            if (array_key_exists($argName, $additionalArgs)) {
                $args[$argName] = $additionalArgs[$argName];
            } else {
                if (!$this->context->has($argName) && !$param->isOptional()) {
                    throw new \Exception('...');
                }
                $args[$argName] = $this->context->get($argName);
            }
        }
        
        return $args;
    }
}