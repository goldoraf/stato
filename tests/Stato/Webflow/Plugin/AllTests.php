<?php
namespace Stato\Webflow\Plugin;


use Stato\TestSuite;

require_once __DIR__ . '/../../TestsHelper.php';

class AllTests
{
    public static function suite()
    {
        $suite = new TestSuite('Stato Webflow Plugins');
        $suite->addTestSuite('Stato\Webflow\Plugin\BrokerTest');
        return $suite;
    }
}
