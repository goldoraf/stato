<?php

require_once dirname(__FILE__).'/TestsHelper.php';

require_once dirname(__FILE__).'/../webflow/tests/AllTests.php';
require_once dirname(__FILE__).'/../orm/tests/AllTests.php';

class Stato_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Stato framework');
        $suite->addTestSuite('Stato_Webflow_AllTests');
        $suite->addTestSuite('Stato_Orm_AllTests');
        return $suite;
    }
}