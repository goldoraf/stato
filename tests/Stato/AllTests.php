<?php



require_once dirname(__FILE__) . '/TestsHelper.php';

class Stato_AllTests
{
    public static function suite()
    {
        $suite = new Stato_TestSuite('Stato_');
        $suite->addTest(Stato_I18n_AllTests::suite());
        $suite->addTest(Stato_Webflow_AllTests::suite());
        $suite->addTest(Stato_Cli_AllTests::suite());
        $suite->addTest(Stato_Mailer_AllTests::suite());

        return $suite;
    }
}
