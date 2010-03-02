<?php





require_once dirname(__FILE__) . '/../../TestsHelper.php';

class Stato_Webflow_Plugin_AllTests
{
    public static function suite()
    {
        $suite = new Stato_TestSuite('Stato Webflow Plugins');
        $suite->addTestSuite('Stato_Webflow_Plugin_BrokerTest');
        return $suite;
    }
}
