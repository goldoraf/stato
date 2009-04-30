<?php

namespace Stato\Mailer;

use Stato\TestSuite;
use Stato\TestEnv;

require_once __DIR__ . '/../TestsHelper.php';

class AllTests
{
    public static function suite()
    {
        $suite = new TestSuite('Stato Mailer');
        $suite->addTestSuite(Mime\AllTests::suite());
        $suite->addTestSuite('Stato\Mailer\MailTest');
        try {
            $conf = TestEnv::getConfig('mailer', 'smtp');
        } catch (\Stato\TestsConfigFileNotFound $e) {
            $suite->markTestSuiteSkipped('You need a TestConfig.php file to run mailer SMTP tests !');
            return $suite;
        } catch (\Stato\TestConfigNotFound $e) {
            $suite->markTestSuiteSkipped('Your TestConfig.php file does not appear to contain SMTP tests params !');
            return $suite;
        }
        $suite->addTestSuite('Stato\Mailer\Transport\SmtpTest');
        $suite->addTestSuite('Stato\Mailer\MailerTest');
        return $suite;
    }
}
