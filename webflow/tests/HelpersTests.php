<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'helpers/StringHelperTest.php';
require_once 'helpers/NumberHelperTest.php';
require_once 'helpers/FormHelperTest.php';

class Stato_Webflow_HelpersTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Stato webflow helpers');
        $suite->addTestSuite('Stato_StringHelperTest');
        $suite->addTestSuite('Stato_NumberHelperTest');
        $suite->addTestSuite('Stato_FormHelperTest');
        return $suite;
    }
}
