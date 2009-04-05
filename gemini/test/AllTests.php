<?php

require_once dirname(__FILE__) . '/../../test/tests_helper.php';

require_once 'FiltersTest.php';
require_once 'MimeTypeTest.php';
require_once 'RequestTest.php';
require_once 'RescueTest.php';
require_once 'RoutesTest.php';
require_once 'SerializersTest.php';
require_once 'UrlRewriterTest.php';
require_once 'HelpersTests.php';
require_once 'FormTests.php';

class StatoWebflowAllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Stato webflow package');
        $suite->addTestSuite('FiltersTest');
        $suite->addTestSuite('MimeTypeTest');
        $suite->addTestSuite('RequestTest');
        $suite->addTestSuite('RescueTest');
        $suite->addTestSuite('RoutesTest');
        $suite->addTestSuite('SerializersTest');
        $suite->addTestSuite('UrlRewriterTest');
        $suite->addTestSuite('HelpersTests');
        $suite->addTestSuite('FormTests');
        return $suite;
    }
}
