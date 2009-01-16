<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'helpers/StringHelperTest.php';

class Stato_Webflow_HelpersTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Stato webflow helpers');
        $suite->addTestSuite('Stato_StringHelperTest');
        return $suite;
    }
}
