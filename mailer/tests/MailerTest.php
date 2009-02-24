<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'mailer.php';
require_once 'mail.php';
require_once 'part.php';
require_once 'attachment.php';
require_once 'mime.php';

require_once dirname(__FILE__).'/files/user_mailer.php';

class Stato_MailerTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        Stato_Mailer::setTemplateRoot(dirname(__FILE__).'/files');
        $this->user = new stdClass;
        $this->user->name = 'John Doe';
        $this->user->mail = 'john.doe@fake.net';
        $this->mailer = new UserMailer();
    }
    
    public function testRenderPlainMessage()
    {
        $mail = $this->mailer->prepareWelcomeMessage($this->user);
        $this->assertEquals('Welcome John Doe', $mail->getBody());
    }
    
    public function testRenderHtmlMessage()
    {
        $mail = $this->mailer->prepareGreetingsMessage($this->user);
        $this->assertEquals('Greetings <b>John Doe</b>', $mail->getBody());
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
}
