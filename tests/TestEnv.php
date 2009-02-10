<?php

class Stato_DbTestsConfigFileNotFound extends Exception {}
class Stato_DbDriverConfigNotFound extends Exception {}

class Stato_TestEnv
{
    private static $dbTestsConfig = null;
    private static $currentTestedDriver = null;
    
    public static function getDbTestsConfig()
    {
        if (self::$dbTestsConfig === null) {
            if (!file_exists(dirname(__FILE__) . '/../orm/tests/Config.php'))
                throw new Stato_DbTestsConfigFileNotFound();
            self::$dbTestsConfig = include dirname(__FILE__) . '/../orm/tests/Config.php';
        }
        return self::$dbTestsConfig;
    }
    
    public static function getDbDriversToTest()
    {
        $conf = self::getDbTestsConfig();
        return array_keys($conf);
    }
    
    public static function setCurrentTestedDriver($driverName)
    {
        self::$currentTestedDriver = $driverName;
    }
    
    public static function getDbDriverConfig($driverName = null)
    {
        $conf = self::getDbTestsConfig();
        if ($driverName === null) {
            if (self::$currentTestedDriver === null) {
                $drivers = self::getDbDriversToTest();
                $driverName = array_shift($drivers);
            } else {
                $driverName = self::$currentTestedDriver;
            }
        }
        if (!array_key_exists($driverName, $conf))
            throw new Stato_DbDriverConfigNotFound($driverName);
            
        return $conf[$driverName];
    }
}