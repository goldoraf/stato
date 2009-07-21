<?php

require_once dirname(__FILE__) . '/../../test/TestsHelper.php';

require_once 'ActiveRecordTest.php';
require_once 'BelongsToTest.php';
require_once 'CallbacksTest.php';
require_once 'DecoratorsTest.php';
require_once 'DirtyRecordTest.php';
require_once 'QuerySetTest.php';
require_once 'EagerLoadingTest.php';
require_once 'HasManyTest.php';
require_once 'HasManyThroughTest.php';
require_once 'HasOneTest.php';
require_once 'ManyToManyTest.php';
require_once 'InheritanceTest.php';
require_once 'MigrationTest.php';
require_once 'SerializationTest.php';
require_once 'ListDecoratorTest.php';
require_once 'TreeDecoratorTest.php';
require_once 'CsvTest.php';

class StatoOrmAllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Stato ORM package');
        $suite->addTestSuite('ActiveRecordTest');
        $suite->addTestSuite('CallbacksTest');
        $suite->addTestSuite('DecoratorsTest');
        $suite->addTestSuite('DirtyRecordTest');
        $suite->addTestSuite('QuerySetTest');
        $suite->addTestSuite('EagerLoadingTest');
        $suite->addTestSuite('BelongsToTest');
        $suite->addTestSuite('HasManyTest');
        $suite->addTestSuite('HasManyThroughTest');
        $suite->addTestSuite('HasOneTest');
        $suite->addTestSuite('ManyToManyTest');
        $suite->addTestSuite('InheritanceTest');
        $suite->addTestSuite('MigrationTest');
        $suite->addTestSuite('SerializationTest');
        $suite->addTestSuite('ListDecoratorTest');
        $suite->addTestSuite('TreeDecoratorTest');
        $suite->addTestSuite('CsvTest');
        return $suite;
    }
}
