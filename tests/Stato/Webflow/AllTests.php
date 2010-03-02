<?php





require_once dirname(__FILE__) . '/../TestsHelper.php';

class Stato_Webflow_AllTests
{
    public static function suite()
    {
        $suite = new Stato_TestSuite('Stato Webflow');
        $suite->addTestSuite('Stato_Webflow_RequestTest');
        $suite->addTestSuite('Stato_Webflow_ResponseTest');
        $suite->addTestSuite('Stato_Webflow_ControllerTest');
        $suite->addTestSuite('Stato_Webflow_FilterChainTest');
        $suite->addTestSuite('Stato_Webflow_RouteSetTest');
        $suite->addTestSuite('Stato_Webflow_FlashTest');
        $suite->addTestSuite('Stato_Webflow_DispatcherTest');
        $suite->addTest(Stato_Webflow_Helper_AllTests::suite());
        $suite->addTest(Stato_Webflow_Forms_AllTests::suite());
        $suite->addTest(Stato_Webflow_Plugin_AllTests::suite());
        return $suite;
    }
}
