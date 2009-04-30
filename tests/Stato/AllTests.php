<?php

namespace Stato;

require_once __DIR__ . '/TestsHelper.php';

class AllTests
{
    public static function suite()
    {
        $suite = new TestSuite('Stato');
        $suite->addTest(I18n\AllTests::suite());
        $suite->addTest(Webflow\AllTests::suite());
        $suite->addTest(Cli\AllTests::suite());
        $suite->addTest(Mailer\AllTests::suite());
        $suite->addTest(Orm\AllTests::suite());
        return $suite;
    }
}
