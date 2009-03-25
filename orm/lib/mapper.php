<?php

class Stato_Mapper
{
    private static $mapping = array();
    
    public static function addClass($class, Stato_Table $table)
    {
        self::$mapping[$class] = $table;
    }
    
    public static function getTable($class)
    {
        return self::$mapping[$class];
    }
}