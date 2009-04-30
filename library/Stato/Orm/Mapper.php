<?php

namespace Stato\Orm;

require_once 'Schema.php';

class Mapper
{
    private static $mapping = array();
    
    public static function addClass($class, Table $table)
    {
        self::$mapping[$class] = $table;
    }
    
    public static function getTable($class)
    {
        return self::$mapping[$class];
    }
}