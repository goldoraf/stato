<?php

namespace Stato\Cli\Command;

use Stato\TestSuite;

require_once __DIR__ . '/../../TestsHelper.php';

class AllTests
{
    public static function suite()
    {
        $suite = new TestSuite('Stato CLI Commands');
        $suite->addTestSuite('Stato\Cli\Command\CreateappTest');
        $suite->addTestSuite('Stato\Cli\Command\MakemessagesTest');
        return $suite;
    }
}
