<?php

namespace Stato\Webflow;

use Stato\TestSuite;

require_once __DIR__ . '/../TestsHelper.php';

class AllTests
{
    public static function suite()
    {
        $suite = new TestSuite('Stato Webflow');
        $suite->addTestSuite('Stato\Webflow\RequestTest');
        $suite->addTestSuite('Stato\Webflow\ResponseTest');
        $suite->addTestSuite('Stato\Webflow\ControllerTest');
        $suite->addTestSuite('Stato\Webflow\FilterChainTest');
        $suite->addTestSuite('Stato\Webflow\RouteSetTest');
        $suite->addTestSuite('Stato\Webflow\FlashTest');
        $suite->addTestSuite('Stato\Webflow\DispatcherTest');
        $suite->addTest(Helper\AllTests::suite());
        $suite->addTest(Forms\AllTests::suite());
        $suite->addTest(Plugin\AllTests::suite());
        return $suite;
    }
}
