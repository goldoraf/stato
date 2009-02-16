<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'MailTest.php';

class Stato_Mailer_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Stato mailer');
        $suite->addTestSuite('Stato_MailTest');
        return $suite;
    }
}
