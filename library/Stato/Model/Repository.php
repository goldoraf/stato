<?php

namespace Stato\Model;

use Exception;

class RepositoryNotSetupException extends Exception {}

class Repository
{
    private static $adapters = array();
    
    private static $instances = array();
    
    public static function setup($repositoryName, $config)
    {
        self::$adapters[$repositoryName] = self::instantiateAdapter($config);
        self::$instances[$repositoryName] = new Repository($repositoryName);
    }
    
    public static function get($name = null)
    {
        if (is_null($name)) {
            $name = self::getDefaultName();
        }
        if (!array_key_exists($name, self::$instances)) {
            throw new RepositoryNotSetupException($name);
        }
        return self::$instances[$name];
    }
    
    public static function getDefaultName()
    {
        return 'default';
    }
    
    private static function instantiateAdapter($config)
    {
        if (!array_key_exists('driver', $config)) {
            throw new Exception("Please provide a 'driver' option for repository setup");
        }
        $driverName = strtolower($config['driver']);
        if (in_array($driverName, array('mysql'))) {
            $adapterClass = __NAMESPACE__ . '\Adapters\Orm';
        } else {
            throw new Exception("No adapter found for '$driverName' driver");
        }
        return new $adapterClass($config);
    }
    
    private $name;
    
    public function __construct($name)
    {
        $this->name = $name;
    }
    
    public function getAdapter()
    {
        return self::$adapters[$this->name];
    }
    
    public function create($object)
    {
        return $this->getAdapter()->create($object);
    }
}