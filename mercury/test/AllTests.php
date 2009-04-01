<?php

require_once dirname(__FILE__) . '/../../test/tests_helper.php';

require_once 'ActiveRecordTest.php';
require_once 'BelongsToTest.php';
require_once 'CallbacksTest.php';
require_once 'DecoratorsTest.php';
require_once 'DirtyRecordTest.php';
require_once 'EagerLoadingTest.php';
require_once 'HasManyTest.php';
require_once 'HasManyThroughTest.php';
require_once 'HasOneTest.php';
require_once 'ListDecoratorTest.php';
require_once 'ManyToManyTest.php';
require_once 'MigrationTest.php';
require_once 'QuerySetTest.php';
require_once 'SerializationTest.php';
require_once 'TreeDecoratorTest.php';

class StatoMercuryAllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Stato Mercury');
        $suite->addTestSuite('ActiveRecordTest');
        $suite->addTestSuite('BelongsToTest');
        $suite->addTestSuite('CallbacksTest');
        $suite->addTestSuite('DecoratorsTest');
        $suite->addTestSuite('DirtyRecordTest');
        $suite->addTestSuite('EagerLoadingTest');
        $suite->addTestSuite('HasManyTest');
        $suite->addTestSuite('HasManyThroughTest');
        $suite->addTestSuite('HasOneTest');
        $suite->addTestSuite('ListDecoratorTest');
        $suite->addTestSuite('ManyToManyTest');
        $suite->addTestSuite('MigrationTest');
        $suite->addTestSuite('QuerySetTest');
        $suite->addTestSuite('SerializationTest');
        $suite->addTestSuite('TreeDecoratorTest');
        return $suite;
    }
}
