<?php

require_once dirname(__FILE__) . '/../../test/TestsHelper.php';

require_once 'DateTest.php';
require_once 'EncryptionTest.php';
require_once 'InflectionTest.php';

class StatoCommonAllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Stato common classes');
        $suite->addTestSuite('DateTest');
        $suite->addTestSuite('EncryptionTest');
        $suite->addTestSuite('InflectionTest');
        return $suite;
    }
}
