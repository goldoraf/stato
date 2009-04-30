<?php

namespace Stato\Mailer\Transport;

use Stato\TestCase;
use Stato\TestEnv;
use Stato\Mailer\Mail;

require_once __DIR__ . '/../../TestsHelper.php';

class SmtpTest extends TestCase
{
    public function setup()
    {
        $conf = TestEnv::getConfig('mailer', 'smtp');
        $this->smtp = new Smtp($conf['host'], $conf);
    }
    
    public function testSend()
    {
        $mail = new Mail();
        $mail->setFrom('root@localhost');
        $mail->addTo('root@localhost', 'John Doe');
        $mail->setText('test');
        $mail->setHtmlText('<b>test</b>');
        $this->assertTrue($mail->send($this->smtp));
    }
}