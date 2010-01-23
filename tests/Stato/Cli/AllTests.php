<?php





require_once dirname(__FILE__) . '/../TestsHelper.php';

class Stato_Cli_AllTests
{
    public static function suite()
    {
        $suite = new Stato_TestSuite('Stato CLI');
        $suite->addTestSuite('Stato_Cli_OptionParserTest');
        $suite->addTestSuite('Stato_Cli_CommandTest');
        $suite->addTestSuite('Stato_Cli_CommandRunnerTest');
        $suite->addTest(Stato_Cli_Command_AllTests::suite());
        return $suite;
    }
}
