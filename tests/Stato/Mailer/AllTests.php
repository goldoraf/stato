<?php






require_once dirname(__FILE__) . '/../TestsHelper.php';

class Stato_Mailer_AllTests
{
    public static function suite()
    {
        $suite = new Stato_TestSuite('Stato Mailer');
        $suite->addTestSuite(Stato_Mailer_Mime_AllTests::suite());
        $suite->addTestSuite('Stato_Mailer_MailTest');
        try {
            $conf = Stato_TestEnv::getConfig('mailer', 'smtp');
        } catch (Stato_TestsConfigFileNotFound $e) {
            $suite->markTestSuiteSkipped('You need a TestConfig.php file to run mailer SMTP tests !');
            return $suite;
        } catch (Stato_TestConfigNotFound $e) {
            $suite->markTestSuiteSkipped('Your TestConfig.php file does not appear to contain SMTP tests params !');
            return $suite;
        }
        $suite->addTestSuite('Stato_Mailer_Transport_SmtpTest');
        $suite->addTestSuite('Stato_Mailer_MailerTest');
        return $suite;
    }
}
