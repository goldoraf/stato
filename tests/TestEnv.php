<?php

class Stato_DbTestsConfigFileNotFound extends Exception {}
class Stato_DbDriverConfigNotFound extends Exception {}

class Stato_TestEnv
{
    private static $testsConfig = null;
    private static $currentTestedDriver = null;
    
    public static function getTestsConfig()
    {
        if (self::$testsConfig === null) {
            if (!file_exists(dirname(__FILE__) . '/TestConfig.php'))
                throw new Stato_DbTestsConfigFileNotFound();
            self::$testsConfig = include dirname(__FILE__) . '/TestConfig.php';
        }
        return self::$testsConfig;
    }
    
    public static function getDbTestsConfig()
    {
        $conf = self::getTestsConfig();
        return $conf['orm'];
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