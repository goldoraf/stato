<?php







require_once dirname(__FILE__) . '/../../TestsHelper.php';

class Stato_Mailer_Transport_SmtpTest extends Stato_TestCase
{
    public function setup()
    {
        $conf = Stato_TestEnv::getConfig('mailer', 'smtp');
        $this->smtp = new Stato_Mailer_Transport_Smtp($conf['host'], $conf);
    }
    
    public function testSend()
    {
        $mail = new Stato_Mailer_Mail();
        $mail->setFrom('root@localhost');
        $mail->addTo('root@localhost', 'John Doe');
        $mail->setText('test');
        $mail->setHtmlText('<b>test</b>');
        $this->assertTrue($mail->send($this->smtp));
    }
}