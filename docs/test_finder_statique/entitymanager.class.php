<?php

class EntityManager
{
    public static $models = array();
    
    public static function load($entityName)
    {
        $modelClass = $entityName.'Model';
        if (class_exists($modelClass))
        {
            self::$models[$entityName] = new $modelClass();
            self::generate($entityName);
        }
    }
    
    public static function get($entityName)
    {
        if (isset(self::$models[$entityName])) return self::$models[$entityName];
    }
    
    protected static function generate($entityName)
    {
        $str = 'class '.$entityName.'Finder extends EntityFinder {'."\n"
        .'public static function findAll() { return parent::find(\'all\', \''.$entityName.'\'); }'
        .'public static function findFirst() { return parent::find(\'first\', \''.$entityName.'\'); } }';
        eval($str);
    }
}

?>
