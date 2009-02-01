<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'dialects/MysqlDialectTest.php';

class Stato_Orm_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Stato ORM');
        $suite->addTestSuite('Stato_MysqlDialectTest');
        return $suite;
    }
}
