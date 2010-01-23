<?php





require_once dirname(__FILE__) . '/../../TestsHelper.php';

class Stato_Webflow_Forms_AllTests
{
    public static function suite()
    {
        $suite = new Stato_TestSuite('Stato Webflow Forms');
        $suite->addTestSuite('Stato_Webflow_Forms_InputsTest');
        $suite->addTestSuite('Stato_Webflow_Forms_FieldsTest');
        $suite->addTestSuite('Stato_Webflow_Forms_ErrorsTest');
        $suite->addTestSuite('Stato_Webflow_Forms_FormTest');
        return $suite;
    }
}
