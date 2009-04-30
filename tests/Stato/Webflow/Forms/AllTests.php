<?php

namespace Stato\Webflow\Forms;

use Stato\TestSuite;

require_once __DIR__ . '/../../TestsHelper.php';

class AllTests
{
    public static function suite()
    {
        $suite = new TestSuite('Stato Webflow Forms');
        $suite->addTestSuite('Stato\Webflow\Forms\InputsTest');
        $suite->addTestSuite('Stato\Webflow\Forms\FieldsTest');
        $suite->addTestSuite('Stato\Webflow\Forms\ErrorsTest');
        $suite->addTestSuite('Stato\Webflow\Forms\FormTest');
        return $suite;
    }
}
