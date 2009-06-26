<?php

namespace Stato\I18n;

use Stato\TestSuite;

require_once __DIR__ . '/../TestsHelper.php';

class AllTests
{
    public static function suite()
    {
        $suite = new TestSuite('Stato i18n');
        $suite->addTestSuite('Stato\I18n\I18nTest');
        $suite->addTestSuite('Stato\I18n\IntlIntegrationTest');
        $suite->addTestSuite('Stato\I18n\Backend\SimpleTest');
        $suite->addTestSuite('Stato\I18n\Backend\YamlTest');
        $suite->addTestSuite('Stato\I18n\Backend\XliffTest');
        return $suite;
    }
}