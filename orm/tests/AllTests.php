<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'DefaultCompilerTest.php';
require_once 'ConnectionTest.php';
require_once 'QueryTest.php';
require_once 'ActiveRecordTest.php';

class Stato_Orm_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Stato ORM');
        try {
            $driversToTest = Stato_TestEnv::getDbDriversToTest();
        } catch (Stato_TestsConfigFileNotFound $e) {
            $suite->markTestSuiteSkipped('You need a TestConfig.php file to run ORM tests !');
            return $suite;
        } catch (Stato_TestConfigNotFound $e) {
            $suite->markTestSuiteSkipped('Your TestConfig.php file does not appear to contain ORM tests params !');
            return $suite;
        }
        
        $suite->addTestSuite('Stato_DefaultCompilerTest');
        
        foreach ($driversToTest as $driver) {
            Stato_TestEnv::setCurrentTestedDriver($driver);
            $driverSuite = new PHPUnit_Framework_TestSuite("$driver driver");
            
            $dialectTest = ucfirst($driver).'DialectTest';
            $dialectTestFile = dirname(__FILE__)."/dialects/{$dialectTest}.php";
            if (file_exists($dialectTestFile)) {
                require_once $dialectTestFile;
                $dialectTestClass = 'Stato_'.$dialectTest;
                $driverSuite->addTestSuite($dialectTestClass);
            }
            
            $driverSuite->addTestSuite('Stato_ConnectionTest');
            
            self::createTestDatabase();
            
            $driverSuite->addTestSuite('Stato_QueryTest');
            $driverSuite->addTestSuite('Stato_ActiveRecordTest');
            
            $suite->addTestSuite($driverSuite);
        }
        
        return $suite;
    }
    
    private static function createTestDatabase()
    {
        $config = Stato_TestEnv::getDbDriverConfig();
        $connection = new Stato_Connection($config);
        $dbname = $config['dbname'];
        $connection->execute("DROP DATABASE IF EXISTS $dbname");
        $connection->execute("CREATE DATABASE $dbname");
        $connection->execute("USE $dbname");
        $tables = include_once 'files/schema.php';
        foreach ($tables as $table) $connection->createTable($table);
    }
}
