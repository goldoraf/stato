<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'RequestTest.php';
require_once 'ResponseTest.php';
require_once 'ControllerTest.php';
require_once 'FiltersTest.php';
require_once 'RouteSetTest.php';
require_once 'FlashTest.php';
require_once 'DispatcherTest.php';
require_once 'HelpersTests.php';

class Stato_Webflow_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Stato webflow');
        $suite->addTestSuite('Stato_RequestTest');
        $suite->addTestSuite('Stato_ResponseTest');
        $suite->addTestSuite('Stato_ControllerTest');
        $suite->addTestSuite('Stato_FiltersTest');
        $suite->addTestSuite('Stato_RouteSetTest');
        $suite->addTestSuite('Stato_FlashTest');
        $suite->addTestSuite('Stato_DispatcherTest');
        $suite->addTestSuite('Stato_Webflow_HelpersTests');
        return $suite;
    }
}
