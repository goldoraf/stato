<?php





require_once dirname(__FILE__) . '/../../TestsHelper.php';

class Stato_Cli_Command_AllTests
{
    public static function suite()
    {
        $suite = new Stato_TestSuite('Stato CLI Commands');
        $suite->addTestSuite('Stato_Cli_Command_CreateappTest');
        $suite->addTestSuite('Stato_Cli_Command_MakemessagesTest');
        return $suite;
    }
}
