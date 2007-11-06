<?php

class SMapper
{
    private static $cache = array();
    
    public static function add_manager_to_class($class)
    {
        $ref = new ReflectionClass($class);
        if ($ref->hasProperty('objects')) 
            $ref->setStaticPropertyValue('objects', self::retrieve_manager($class));
    }
    
    public static function retrieve($class)
    {
        if (!isset(self::$cache[$class]))
            self::$cache[$class] = new STableMap($class);
        
        return self::$cache[$class];
    }
    
    public static function retrieve_manager($class)
    {
        $manager_class = $class.'Manager';
        if (class_exists($manager_class, false)) return new $manager_class($class);
        return new SManager($class);
    }
    
    public static function reset_meta_information($class)
    {
        unset(self::$cache[$class]);
        SActiveRecord::connection()->reset_columns_cache();
    }
}

?>
