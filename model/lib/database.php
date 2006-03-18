<?php

class SDatabase
{
    static private $instance = Null;
	
	public static function getInstance()
    {
       if (!isset(self::$instance))
       {
            $config = include(ROOT_DIR.'/conf/database.php');
            $driverClass = 'S'.$config[APP_MODE]['driver'].'Driver';
            if (class_exists($driverClass))
            {
                self::$instance = new $driverClass($config[APP_MODE]);
                self::$instance->connect();
            }
            else
            {
                throw new SException('Database driver not found !');
            }
       }
       return self::$instance;
    }
}

?>
