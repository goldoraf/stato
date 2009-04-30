<?php

namespace Stato\Mailer\Mime;

use Stato\TestSuite;

require_once __DIR__ . '/../../TestsHelper.php';

class AllTests
{
    public static function suite()
    {
        $suite = new TestSuite('Stato Mime');
        $suite->addTestSuite('Stato\Mailer\Mime\MimeTest');
        $suite->addTestSuite('Stato\Mailer\Mime\EntityTest');
        $suite->addTestSuite('Stato\Mailer\Mime\PartTest');
        $suite->addTestSuite('Stato\Mailer\Mime\AttachmentTest');
        $suite->addTestSuite('Stato\Mailer\Mime\MultipartTest');
        return $suite;
    }
}