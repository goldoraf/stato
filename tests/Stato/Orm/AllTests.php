<?php

namespace Stato\Orm;

use Stato\TestSuite;
use Stato\TestEnv;

require_once __DIR__ . '/../TestsHelper.php';

class AllTests
{
    public static function suite()
    {
        $suite = new TestSuite('Stato ORM');
        try {
            $driversToTest = TestEnv::getDbDriversToTest();
        } catch (\Stato\TestsConfigFileNotFound $e) {
            $suite->markTestSuiteSkipped('You need a TestConfig.php file to run ORM tests !');
            return $suite;
        } catch (\Stato\TestConfigNotFound $e) {
            $suite->markTestSuiteSkipped('Your TestConfig.php file does not appear to contain ORM tests params !');
            return $suite;
        }
        
        $suite->addTestSuite('Stato\Orm\CompilerTest');
        
        foreach ($driversToTest as $driver) {
            TestEnv::setCurrentTestedDriver($driver);
            $driverSuite = new TestSuite("$driver driver");
            
            $dialectTestSuite = 'Stato\Orm\Dialect\\'.ucfirst($driver).'Test';
            $driverSuite->addTestSuite($dialectTestSuite);
            
            $driverSuite->addTestSuite('Stato\Orm\ConnectionTest');
            $driverSuite->addTestSuite('Stato\Orm\QueryTest');
            $driverSuite->addTestSuite('Stato\Orm\EntityTest');
            $driverSuite->addTestSuite('Stato\Orm\RelationsTest');
            
            $suite->addTestSuite($driverSuite);
        }
        
        return $suite;
    }
}
