<?php





require_once dirname(__FILE__) . '/../../TestsHelper.php';

class Stato_Mailer_Mime_AllTests
{
    public static function suite()
    {
        $suite = new Stato_TestSuite('Stato Mime');
        $suite->addTestSuite('Stato_Mailer_Mime_MimeTest');
        $suite->addTestSuite('Stato_Mailer_Mime_EntityTest');
        $suite->addTestSuite('Stato_Mailer_Mime_PartTest');
        $suite->addTestSuite('Stato_Mailer_Mime_AttachmentTest');
        $suite->addTestSuite('Stato_Mailer_Mime_MultipartTest');
        return $suite;
    }
}