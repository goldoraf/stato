<?php

namespace Stato\Cli;

use Stato\TestSuite;

require_once __DIR__ . '/../TestsHelper.php';

class AllTests
{
    public static function suite()
    {
        $suite = new TestSuite('Stato CLI');
        $suite->addTestSuite('Stato\Cli\OptionParserTest');
        $suite->addTestSuite('Stato\Cli\CommandTest');
        $suite->addTestSuite('Stato\Cli\CommandRunnerTest');
        $suite->addTest(Command\AllTests::suite());
        return $suite;
    }
}
