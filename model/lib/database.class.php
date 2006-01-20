<?php

/**
 * Database
 * 
 * Just a DbFactory using Singleton pattern.
 * May be replaced by a class using PDO.
 * 
 * @package 
 * @author Raphaël Rougeron
 * @copyright Copyright (c) 2005
 * @version 0.1
 * @access public
 **/
class Database
{
    static private $instance = Null;
	
	public static function getInstance()
    {
       if (!isset(self::$instance))
       {
            $config = include(ROOT_DIR.'/conf/database.php');
            $driverClass = $config['driver'].'Driver';
            if (class_exists($driverClass))
            {
                self::$instance = new $driverClass($config);
                self::$instance->connect();
            }
            else
            {
                throw new Exception('Database driver not found !');
            }
       }
       return self::$instance;
    }
    
    public static function quote($value)
    {
        if (!isset(self::$instance)) self::getInstance();
        return self::$instance->quote($value);
    }
}

?>
