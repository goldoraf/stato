<?php

namespace Stato\Webflow\Helper;

use Stato\TestSuite;

require_once __DIR__ . '/../../TestsHelper.php';

class AllTests
{
    public static function suite()
    {
        $suite = new TestSuite('Stato Webflow Helpers');
        $suite->addTestSuite('Stato\Webflow\Helper\StringTest');
        return $suite;
    }
}
