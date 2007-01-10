<?php

class SMapper
{
    private static $cache = array();
    
    public static function add_manager_to_class($class)
    {
        $ref = new ReflectionClass($class);
        if ($ref->hasProperty('objects')) $ref->setStaticPropertyValue('objects', new SManager($class));
    }
    
    public static function retrieve($class)
    {
        if (!isset(self::$cache[$class]))
            self::$cache[$class] = new STableMap($class);
        
        return self::$cache[$class];
    }
    
    public static function reset_meta_information($class)
    {
        unset(self::$cache[$class]);
    }
}

?>
