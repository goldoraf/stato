<?php

namespace Stato\Mailer;

use Stato\TestCase;
use UserMailer;

require_once __DIR__ . '/../TestsHelper.php';

require_once __DIR__ . '/files/user_mailer.php';
require_once __DIR__ . '/files/dummy_transport.php';

class MailerTest extends TestCase
{
    public function setup()
    {
        Mailer::setTemplateRoot(dirname(__FILE__).'/files');
        Mailer::setDefaultTransport(new Transport\Dummy());
        $this->user = new \stdClass;
        $this->user->name = 'John Doe';
        $this->user->mail = 'john.doe@fake.net';
    }
    
    public function testRenderPlainMessage()
    {
        $mail = UserMailer::prepareWelcomeMessage($this->user);
        $this->assertEquals('Welcome John Doe', $mail->getContent());
    }
    
    public function testRenderHtmlMessage()
    {
        $mail = UserMailer::prepareGreetingsMessage($this->user);
        $this->assertEquals('Greetings <b>John Doe</b>', $mail->getContent());
    }
    
    public function testRenderMissingTemplateShouldThrow()
    {
        $this->setExpectedException('Stato\Mailer\Exception');
        $mail = UserMailer::prepareForgotPasswordMessage($this->user);
    }
    
    public function testRenderBodyWhenTemplateRootNotSetShouldThrow()
    {
        $this->setExpectedException('Stato\Mailer\Exception', 'Template root not set');
        Mailer::setTemplateRoot(null);
        $mail = UserMailer::prepareGreetingsMessage($this->user);
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
        $this->assertEquals($message, UserMailer::sendTestMessage());
    }
    
    public function testTextShortcuts()
    {
        $message = <<<EOT
Date: Fri, 13 Feb 09 15:47:25 +0100
MIME-Version: 1.0
From: notifications@dummysite.com
To: john.doe@fake.net
Subject: Welcome to our site
Content-Type: multipart/alternative; boundary="c67476988f320ca04d61815bcfd14360"

This is a multi-part message in MIME format.
--c67476988f320ca04d61815bcfd14360
Content-Type: text/plain; charset="UTF-8"
Content-Transfer-Encoding: 8bit

Welcome John Doe
--c67476988f320ca04d61815bcfd14360
Content-Type: text/html; charset="UTF-8"
Content-Transfer-Encoding: 8bit

Welcome <b>John Doe</b>
--c67476988f320ca04d61815bcfd14360--
EOT;
        $mail = UserMailer::prepareSignupNotification($this->user);
        $mail->setBoundary('c67476988f320ca04d61815bcfd14360');
        $this->assertEquals($message, $mail->__toString());
    }
    
    public function testPartShortcuts()
    {
        $message = <<<EOT
Date: Fri, 13 Feb 09 15:47:25 +0100
MIME-Version: 1.0
From: notifications@dummysite.com
To: john.doe@fake.net
Subject: Welcome to our site
Content-Type: multipart/mixed; boundary="c67476988f320ca04d61815bcfd14360"

This is a multi-part message in MIME format.
--c67476988f320ca04d61815bcfd14360
Content-Type: text/x-vcard; charset="UTF-8"
Content-Transfer-Encoding: 8bit

BEGIN:VCARD
END:VCARD

--c67476988f320ca04d61815bcfd14360
Content-Type: image/png; name="hello.png"
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="hello.png"

iVBORw0KGgoAAAANSUhEUgAAAAYAAAAFCAYAAABmWJ3mAAAAAXNSR0IArs4c6QAAAAZiS0dE
AP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAIBJREFUCNc9yK8OQWEcx+HPiyCb
JtjciKaZILgARTRXwfQTXIVGM03xZwLZtHN2zpiZ2fu+v6/miY+b7EYyRcwMJMo4us0+lWgB
WcQJEPRaA9qNDqxuS12Ks8bboTb3tSQpRC+e34ckKf9k/0z2U5WS04y3f1Gr1jEZi8Oca3rk
B3WXTGfs7Y8kAAAAAElFTkSuQmCC
--c67476988f320ca04d61815bcfd14360--
EOT;
        $mail = UserMailer::prepareContactNotification($this->user);
        $mail->setBoundary('c67476988f320ca04d61815bcfd14360');
        $this->assertEquals($message, $mail->__toString());
    }
}
