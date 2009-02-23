<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'OptionParserTest.php';
require_once 'CommandTest.php';
require_once 'CommandRunnerTest.php';
require_once 'CreateappCommandTest.php';

class Stato_Cli_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Stato CLI');
        $suite->addTestSuite('Stato_Cli_OptionParserTest');
        $suite->addTestSuite('Stato_Cli_CommandTest');
        $suite->addTestSuite('Stato_Cli_CommandRunnerTest');
        $suite->addTestSuite('Stato_Cli_CreateappCommandTest');
        return $suite;
    }
}
