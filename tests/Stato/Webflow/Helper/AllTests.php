<?php





require_once dirname(__FILE__) . '/../../TestsHelper.php';

class Stato_Webflow_Helper_AllTests
{
    public static function suite()
    {
        $suite = new Stato_TestSuite('Stato Webflow Helpers');
        $suite->addTestSuite('Stato_Webflow_Helper_StringTest');
        return $suite;
    }
}
