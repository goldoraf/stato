<?php



class Stato_TestConfigNotFound extends Exception {}
class Stato_TestsConfigFileNotFound extends Exception {}
class Stato_DbDriverConfigNotFound extends Exception {}

class Stato_TestEnv
{
    private static $testsConfig = null;
    private static $currentTestedDriver = null;
    
    public static function getTestsConfig()
    {
        if (self::$testsConfig === null) {
            if (!file_exists(dirname(__FILE__) . '/TestConfig.php'))
                throw new Stato_TestsConfigFileNotFound();
            self::$testsConfig = include dirname(__FILE__) . '/TestConfig.php';
        }
        return self::$testsConfig;
    }
    
    public static function getConfig($package, $class = null)
    {
        $conf = self::getTestsConfig();
        if (!array_key_exists($package, $conf))
            throw new Stato_TestConfigNotFound($package);
        
        if ($class === null) return $conf[$package];
        
        if (!array_key_exists($class, $conf[$package]))
            throw new Stato_TestConfigNotFound($package.'::'.$class);
            
        return $conf[$package][$class];
    }
    
    public static function getDbTestsConfig()
    {
        return self::getConfig('orm');
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