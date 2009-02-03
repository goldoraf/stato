<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'dialects/MysqlDialectTest.php';

class Stato_Orm_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Stato ORM');
        if (!file_exists(dirname(__FILE__) . '/Config.php')) {
            $suite->markTestSuiteSkipped('You need a Config.php file to run ORM tests !');
            return $suite;
        }
        $suite->addTestSuite('Stato_MysqlDialectTest');
        return $suite;
    }
}
