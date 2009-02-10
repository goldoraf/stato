<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'DefaultCompilerTest.php';
require_once 'ConnectionTest.php';
//require_once 'StatementTest.php';

class Stato_Orm_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Stato ORM');
        try {
            $driversToTest = Stato_TestEnv::getDbDriversToTest();
        } catch (Stato_DbTestsConfigFileNotFound $e) {
            $suite->markTestSuiteSkipped('You need a Config.php file to run ORM tests !');
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
            
            $suite->addTestSuite($driverSuite);
        }
        
        //$suite->addTestSuite('Stato_StatementTestSuite');
        return $suite;
    }
}
