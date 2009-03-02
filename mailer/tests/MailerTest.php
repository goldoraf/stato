<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'mime/mime.php';
require_once 'mime/entity.php';
require_once 'mime/part.php';
require_once 'mime/multipart.php';
require_once 'mail.php';
require_once 'mailer.php';

require_once dirname(__FILE__).'/files/user_mailer.php';
require_once dirname(__FILE__).'/files/dummy_transport.php';

class Stato_MailerTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        Stato_Mailer::setTemplateRoot(dirname(__FILE__).'/files');
        Stato_Mailer::setDefaultTransport(new Stato_DummyTransport());
        $this->user = new stdClass;
        $this->user->name = 'John Doe';
        $this->user->mail = 'john.doe@fake.net';
        $this->mailer = new UserMailer();
    }
    
    public function testRenderPlainMessage()
    {
        $mail = $this->mailer->prepareWelcomeMessage($this->user);
        $this->assertEquals('Welcome John Doe', $mail->getContent());
    }
    
    public function testRenderHtmlMessage()
    {
        $mail = $this->mailer->prepareGreetingsMessage($this->user);
        $this->assertEquals('Greetings <b>John Doe</b>', $mail->getContent());
    }
    
    public function testRenderMissingTemplateShouldThrow()
    {
        $this->setExpectedException('Stato_MailException');
        $mail = $this->mailer->prepareForgotPasswordMessage($this->user);
    }
    
    public function testRenderBodyWhenTemplateRootNotSetShouldThrow()
    {
        $this->setExpectedException('Stato_MailException', 'Template root not set');
        Stato_Mailer::setTemplateRoot(null);
        $mail = $this->mailer->prepareGreetingsMessage($this->user);
    }
    
    public function testSend()
    {
        $message = <<<EOT
Date: Fri, 13 Feb 09 15:47:25 +0100
MIME-Version: 1.0
To: John Doe <john.doe@fake.net>
Content-Type: text/plain; charset="UTF-8"
Content-Transfer-Encoding: 8bit

test
EOT;
        $this->assertEquals($message, $this->mailer->sendTestMessage());
    }
}
