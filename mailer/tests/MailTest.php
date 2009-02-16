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
        Stato_Mail::$eol = "\n";
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
    
    public function testImageAttachment()
    {
        $source = <<<EOT
Date: Fri, 13 Feb 09 15:47:25 +0100
MIME-Version: 1.0
Content-Type: image/png
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="hello.png"

iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0
RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAKfSURBVDjLpZPrS1NhHMf9O3bO
dmwDCWREIYKEUHsVJBI7mg3FvCxL09290jZj2EyLMnJexkgpLbPUanNOberU5taUMnHZUULM
velCtWF0sW/n7MVMEiN64AsPD8/n83uucQDi/id/DBT4Dolypw/qsz0pTMbj/WHpiDgsdSUy
UmeiPt2+V7SrIM+bSss8ySGdR4abQQv6lrui6VxsRonrGCS9VEjSQ9E7CtiqdOZ4UuTqnBHO
1X7YXl6Daa4yGq7vWO1D40wVDtj4kWQbn94myPGkCDPdSesczE2sCZShwl8CzcwZ6NiUs6n2
nYX99T1cnKqA2EKui6+TwphA5k4yqMayopU5mANV3lNQTBdCMVUA9VQh3GuDMHiVcLCS3J4j
SLhCGmKCjBEx0xlshjXYhApfMZRP5CyYD+UkG08+xt+4wLVQZA1tzxthm2tEfD3JxARH7Qkb
D1ZuozaggdZbxK5kAIsf5qGaKMTY2lAU/rH5HW3PLsEwUYy+YCcERmIjJpDcpzb6l7th9KtQ
69fi09ePUej9l7cx2DJbD7UrG3r3afQHOyCo+V3QQzE35pvQvnAZukk5zL5qRL59jsKbPzdh
eXoBZc4saFhBS6AO7V4zqCpiawuptwQG+UAa7Ct3UT0hh9p9EnXT5Vh6t4C22QaUDh6HwnEC
OmcO7K+6kW49DKqS2DrEZCtfuI+9GrNHg4fMHVSO5kE7nAPVkAxKBxcOzsajpS4Yh4ohUPPW
KTUh3PaQEptIOr6BiJjcZXCwktaAGfrRIpwblqOV3YKdhfXOIvBLeREWpnd8ynsaSJoyESFp
hwTtfjN6X1jRO2+FxWtCWksqBApeiFIR9K6fiTpPiigDoadqCEag5YUFKl6Yrciw0VOlhOiv
v/Ff8wtn0KzlebrUYwAAAABJRU5ErkJg
EOT;
        $mail = new Stato_Mail($this->date);
        $mail->addAttachment(array('content_type' => 'image/png', 'filename' => 'hello.png',
                                   'body' => file_get_contents(dirname(__FILE__).'/files/accept.png')));
        $this->assertEquals($source, (string) $mail);
    }
    
    /**
     * TO FIX : there is a difference of 4 chars (gg==) between the base 64 
     * encoded resource and the base 64 encoded string... Why ?
     */
    public function testImageAttachmentWithResource()
    {
        $source = <<<EOT
Date: Fri, 13 Feb 09 15:47:25 +0100
MIME-Version: 1.0
Content-Type: image/png
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="hello.png"

iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0
RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAKfSURBVDjLpZPrS1NhHMf9O3bO
dmwDCWREIYKEUHsVJBI7mg3FvCxL09290jZj2EyLMnJexkgpLbPUanNOberU5taUMnHZUULM
velCtWF0sW/n7MVMEiN64AsPD8/n83uucQDi/id/DBT4Dolypw/qsz0pTMbj/WHpiDgsdSUy
UmeiPt2+V7SrIM+bSss8ySGdR4abQQv6lrui6VxsRonrGCS9VEjSQ9E7CtiqdOZ4UuTqnBHO
1X7YXl6Daa4yGq7vWO1D40wVDtj4kWQbn94myPGkCDPdSesczE2sCZShwl8CzcwZ6NiUs6n2
nYX99T1cnKqA2EKui6+TwphA5k4yqMayopU5mANV3lNQTBdCMVUA9VQh3GuDMHiVcLCS3J4j
SLhCGmKCjBEx0xlshjXYhApfMZRP5CyYD+UkG08+xt+4wLVQZA1tzxthm2tEfD3JxARH7Qkb
D1ZuozaggdZbxK5kAIsf5qGaKMTY2lAU/rH5HW3PLsEwUYy+YCcERmIjJpDcpzb6l7th9KtQ
69fi09ePUej9l7cx2DJbD7UrG3r3afQHOyCo+V3QQzE35pvQvnAZukk5zL5qRL59jsKbPzdh
eXoBZc4saFhBS6AO7V4zqCpiawuptwQG+UAa7Ct3UT0hh9p9EnXT5Vh6t4C22QaUDh6HwnEC
OmcO7K+6kW49DKqS2DrEZCtfuI+9GrNHg4fMHVSO5kE7nAPVkAxKBxcOzsajpS4Yh4ohUPPW
KTUh3PaQEptIOr6BiJjcZXCwktaAGfrRIpwblqOV3YKdhfXOIvBLeREWpnd8ynsaSJoyESFp
hwTtfjN6X1jRO2+FxWtCWksqBApeiFIR9K6fiTpPiigDoadqCEag5YUFKl6Yrciw0VOlhOiv
v/Ff8wtn0KzlebrUYwAAAABJRU5ErkJggg==
EOT;
        $mail = new Stato_Mail($this->date);
        $mail->addAttachment(array('content_type' => 'image/png', 'filename' => 'hello.png',
                                   'body' => fopen(dirname(__FILE__).'/files/accept.png', 'r')));
        $this->assertEquals($source, (string) $mail);
    }
    
    public function testMultipartMessage()
    {
        $headers = <<<EOT
Date: Fri, 13 Feb 09 15:47:25 +0100
MIME-Version: 1.0
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

iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0
RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAKfSURBVDjLpZPrS1NhHMf9O3bO
dmwDCWREIYKEUHsVJBI7mg3FvCxL09290jZj2EyLMnJexkgpLbPUanNOberU5taUMnHZUULM
velCtWF0sW/n7MVMEiN64AsPD8/n83uucQDi/id/DBT4Dolypw/qsz0pTMbj/WHpiDgsdSUy
UmeiPt2+V7SrIM+bSss8ySGdR4abQQv6lrui6VxsRonrGCS9VEjSQ9E7CtiqdOZ4UuTqnBHO
1X7YXl6Daa4yGq7vWO1D40wVDtj4kWQbn94myPGkCDPdSesczE2sCZShwl8CzcwZ6NiUs6n2
nYX99T1cnKqA2EKui6+TwphA5k4yqMayopU5mANV3lNQTBdCMVUA9VQh3GuDMHiVcLCS3J4j
SLhCGmKCjBEx0xlshjXYhApfMZRP5CyYD+UkG08+xt+4wLVQZA1tzxthm2tEfD3JxARH7Qkb
D1ZuozaggdZbxK5kAIsf5qGaKMTY2lAU/rH5HW3PLsEwUYy+YCcERmIjJpDcpzb6l7th9KtQ
69fi09ePUej9l7cx2DJbD7UrG3r3afQHOyCo+V3QQzE35pvQvnAZukk5zL5qRL59jsKbPzdh
eXoBZc4saFhBS6AO7V4zqCpiawuptwQG+UAa7Ct3UT0hh9p9EnXT5Vh6t4C22QaUDh6HwnEC
OmcO7K+6kW49DKqS2DrEZCtfuI+9GrNHg4fMHVSO5kE7nAPVkAxKBxcOzsajpS4Yh4ohUPPW
KTUh3PaQEptIOr6BiJjcZXCwktaAGfrRIpwblqOV3YKdhfXOIvBLeREWpnd8ynsaSJoyESFp
hwTtfjN6X1jRO2+FxWtCWksqBApeiFIR9K6fiTpPiigDoadqCEag5YUFKl6Yrciw0VOlhOiv
v/Ff8wtn0KzlebrUYwAAAABJRU5ErkJg
--c67476988f320ca04d61815bcfd14360--
EOT;
        $mail = new Stato_Mail($this->date);
        $mail->setBody('test');
        $mail->setBoundary('c67476988f320ca04d61815bcfd14360');
        $mail->addAttachment(array('content_type' => 'image/png', 'filename' => 'hello.png',
                                   'body' => file_get_contents(dirname(__FILE__).'/files/accept.png')));
        $this->assertEquals($headers, $mail->prepareHeaders());
        $this->assertEquals($body, $mail->getBody());
    }
}