<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'mail.php';
require_once 'part.php';
require_once 'attachment.php';
require_once 'mime.php';

class Stato_MailTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        Stato_Mail::setTemplateRoot(dirname(__FILE__).'/files');
        $this->date = new DateTime('2009-02-13 15:47:25', new DateTimeZone('Europe/Paris'));
    }
    
    public function testSimpleMessage()
    {
        $headers = <<<EOT
Date: Fri, 13 Feb 09 15:47:25 +0100
MIME-Version: 1.0
From: Foo Bar <foo.bar@dummy.com>
To: John Doe <john.doe@fake.net>
Subject: Stop these useless meetings...
Content-Type: text/plain; charset="utf-8"
Content-Transfer-Encoding: 8bit
Content-Disposition: inline
EOT;
        $mail = new Stato_Mail($this->date);
        $mail->setFrom('foo.bar@dummy.com', 'Foo Bar');
        $mail->addTo('john.doe@fake.net', 'John Doe');
        $mail->setSubject('Stop these useless meetings...');
        $mail->setBody('test');
        $this->assertEquals('test', $mail->getBody());
        $this->assertEquals($headers, $mail->prepareHeaders());
        $this->assertEquals("$headers\n\ntest", (string) $mail);
    }
    
    public function testMessageWithoutABodyShouldThrow()
    {
        $mail = new Stato_Mail($this->date);
        $mail->addTo('john.doe@fake.net', 'John Doe');
        $this->setExpectedException('Stato_MailException', 'No body specified');
        $mail->getBody();
    }
    
    public function testRecipients()
    {
        $headers = <<<EOT
Date: Fri, 13 Feb 09 15:47:25 +0100
MIME-Version: 1.0
From: Foo Bar <foo.bar@dummy.com>
To: John Doe <john.doe@fake.net>
Cc: jane.doe@fake.net, =?UTF-8?Q?Rapha=C3=ABl=20Rougeron?= <not.real@ofcourse.net>
Bcc: bureaucratic.director@bigbrother.com
Subject: Stop these useless meetings...
Content-Type: text/plain; charset="utf-8"
Content-Transfer-Encoding: 8bit
Content-Disposition: inline
EOT;
        $mail = new Stato_Mail($this->date);
        $mail->setFrom('foo.bar@dummy.com', 'Foo Bar');
        $mail->addTo('john.doe@fake.net', 'John Doe');
        $mail->addCc('jane.doe@fake.net');
        $mail->addCc('not.real@ofcourse.net', 'Raphaël Rougeron'); // is the above encoded name correct ? not sure...
        $mail->addBcc('bureaucratic.director@bigbrother.com');
        $mail->setSubject('Stop these useless meetings...');
        $mail->setBody('test');
        $this->assertEquals($headers, $mail->prepareHeaders());
    }
    
    public function testGetHeaderValue()
    {
        $mail = new Stato_Mail($this->date);
        $mail->setFrom('foo.bar@dummy.com', 'Foo Bar');
        $mail->addTo('john.doe@fake.net', 'John Doe');
        $mail->addCc('jane.doe@fake.net');
        $mail->addCc('not.real@ofcourse.net', 'Raphael Rougeron');
        $mail->setSubject('Stop these useless meetings...');
        $this->assertEquals('John Doe <john.doe@fake.net>', $mail->getTo());
        $this->assertEquals('Foo Bar <foo.bar@dummy.com>', $mail->getFrom());
        $this->assertEquals('jane.doe@fake.net, Raphael Rougeron <not.real@ofcourse.net>', $mail->getCc());
        $this->assertEquals('', $mail->getBcc());
        $this->assertEquals('Stop these useless meetings...', $mail->getSubject());
    }
    
    public function testMessageWithoutAToShouldThrow()
    {
        $mail = new Stato_Mail($this->date);
        $this->setExpectedException('Stato_MailException', 'To: recipient is not specified');
        $mail->getTo();
    }
    
    public function testGetHeadersWithExclude()
    {
        $mail = new Stato_Mail($this->date);
        $mail->setFrom('foo.bar@dummy.com');
        $mail->addTo('john.doe@fake.net');
        $mail->setBody('test');
        $this->assertEquals(array(
            'Date' => 'Fri, 13 Feb 09 15:47:25 +0100',
            'MIME-Version' => '1.0',
            'To' => array('john.doe@fake.net'),
            'Content-Type' => 'text/plain; charset="utf-8"',
            'Content-Transfer-Encoding' => '8bit',
            'Content-Disposition' => 'inline'), 
            $mail->getHeaders(array('From'))
        );
    }
    
    public function testHtmlMessage()
    {
        $message = <<<EOT
Date: Fri, 13 Feb 09 15:47:25 +0100
MIME-Version: 1.0
From: Foo Bar <foo.bar@dummy.com>
To: John Doe <john.doe@fake.net>
Content-Type: text/html; charset="utf-8"
Content-Transfer-Encoding: 8bit
Content-Disposition: inline

<b>test</b>
EOT;
        $mail = new Stato_Mail($this->date);
        $mail->setFrom('foo.bar@dummy.com', 'Foo Bar');
        $mail->addTo('john.doe@fake.net', 'John Doe');
        $mail->setHtmlBody('<b>test</b>');
        $this->assertEquals($message, (string) $mail);
    }
    
    public function testMultipartMessage()
    {
        $headers = <<<EOT
Date: Fri, 13 Feb 09 15:47:25 +0100
MIME-Version: 1.0
To: John Doe <john.doe@fake.net>
Content-Type: multipart/mixed; boundary="c67476988f320ca04d61815bcfd14360"
Content-Transfer-Encoding: 8bit
EOT;
        $body = <<<EOT
This is a multi-part message in MIME format.
--c67476988f320ca04d61815bcfd14360
Content-Type: text/plain; charset="utf-8"
Content-Transfer-Encoding: 8bit
Content-Disposition: inline

test
--c67476988f320ca04d61815bcfd14360
Content-Type: text/html; charset="utf-8"
Content-Transfer-Encoding: 8bit
Content-Disposition: inline

<b>test</b>
--c67476988f320ca04d61815bcfd14360--
EOT;
        $mail = new Stato_Mail($this->date);
        $mail->addTo('john.doe@fake.net', 'John Doe');
        $mail->setBody('test');
        $mail->setHtmlBody('<b>test</b>');
        $mail->setBoundary('c67476988f320ca04d61815bcfd14360');
        $this->assertEquals($headers, $mail->prepareHeaders());
        $this->assertEquals($body, $mail->getBody());
    }
    
    public function testAddAttachment()
    {
        $headers = <<<EOT
Date: Fri, 13 Feb 09 15:47:25 +0100
MIME-Version: 1.0
To: John Doe <john.doe@fake.net>
Content-Type: multipart/mixed; boundary="c67476988f320ca04d61815bcfd14360"
Content-Transfer-Encoding: 8bit
EOT;
        $body = <<<EOT
This is a multi-part message in MIME format.
--c67476988f320ca04d61815bcfd14360
Content-Type: text/plain; charset="utf-8"
Content-Transfer-Encoding: 8bit
Content-Disposition: inline

test
--c67476988f320ca04d61815bcfd14360
Content-Type: image/png
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="hello.png"

iVBORw0KGgoAAAANSUhEUgAAAAYAAAAFCAYAAABmWJ3mAAAAAXNSR0IArs4c6QAAAAZiS0dE
AP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAIBJREFUCNc9yK8OQWEcx+HPiyCb
JtjciKaZILgARTRXwfQTXIVGM03xZwLZtHN2zpiZ2fu+v6/miY+b7EYyRcwMJMo4us0+lWgB
WcQJEPRaA9qNDqxuS12Ks8bboTb3tSQpRC+e34ckKf9k/0z2U5WS04y3f1Gr1jEZi8Oca3rk
B3WXTGfs7Y8kAAAAAElFTkSuQmCC
--c67476988f320ca04d61815bcfd14360--
EOT;
        $mail = new Stato_Mail($this->date);
        $mail->addTo('john.doe@fake.net', 'John Doe');
        $mail->setBody('test');
        $mail->setBoundary('c67476988f320ca04d61815bcfd14360');
        $mail->addAttachment(array('content_type' => 'image/png', 'filename' => 'hello.png',
                                   'body' => file_get_contents(dirname(__FILE__).'/files/image.png')));
        $this->assertEquals($headers, $mail->prepareHeaders());
        $this->assertEquals($body, $mail->getBody());
    }
    
    public function testRenderBody()
    {
        $mail = new Stato_Mail($this->date);
        $mail->addTo('john.doe@fake.net', 'John Doe');
        $mail->renderBody('message_template', array('username' => 'raphael'));
        $this->assertEquals('Hello raphael', $mail->getBody());
    }
    
    public function testRenderHtmlBody()
    {
        $mail = new Stato_Mail($this->date);
        $mail->addTo('john.doe@fake.net', 'John Doe');
        $mail->renderHtmlBody('html_message_template', array('username' => 'raphael'));
        $this->assertEquals('Hello <b>raphael</b>', $mail->getBody());
    }
    
    public function testRenderMissingTemplate()
    {
        $this->setExpectedException('Stato_MailException');
        $mail = new Stato_Mail($this->date);
        $mail->addTo('john.doe@fake.net', 'John Doe');
        $mail->renderBody('missing_template', array('username' => 'raphael'));
    }
    
    public function testRenderBodyWhenTemplateRootNotSetShouldThrow()
    {
        $this->setExpectedException('Stato_MailException', 'Template root not set');
        Stato_Mail::setTemplateRoot(null);
        $mail = new Stato_Mail($this->date);
        $mail->addTo('john.doe@fake.net', 'John Doe');
        $mail->renderBody('message_template', array('username' => 'raphael'));
    }
    
    public function testSend()
    {
        Stato_Mail::setDefaultTransport(new Stato_DummyTransport());
        $message = <<<EOT
Date: Fri, 13 Feb 09 15:47:25 +0100
MIME-Version: 1.0
To: John Doe <john.doe@fake.net>
Content-Type: text/plain; charset="utf-8"
Content-Transfer-Encoding: 8bit
Content-Disposition: inline

test
EOT;
        $mail = new Stato_Mail($this->date);
        $mail->addTo('john.doe@fake.net', 'John Doe');
        $mail->setBody('test');
        $this->assertEquals($message, $mail->send());
    }
}

class Stato_DummyTransport implements Stato_IMailTransport
{
    public function send(Stato_Mail $mail)
    {
        return (string) $mail;
    }
}