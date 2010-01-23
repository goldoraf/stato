<?php





require_once dirname(__FILE__) . '/../../TestsHelper.php';

class Stato_Mailer_Mime_MultipartTest extends Stato_TestCase
{
    public function testMultipartAlternative()
    {
        $str = <<<EOT
Content-Type: multipart/alternative; boundary="c67476988f320ca04d61815bcfd14360"

This is a multi-part message in MIME format.
--c67476988f320ca04d61815bcfd14360
Content-Type: text/plain; charset="UTF-8"
Content-Transfer-Encoding: 8bit

test
--c67476988f320ca04d61815bcfd14360
Content-Type: text/html; charset="UTF-8"
Content-Transfer-Encoding: 8bit

<b>test</b>
--c67476988f320ca04d61815bcfd14360--
EOT;
        $mp = new Stato_Mailer_Mime_Multipart();
        $mp->setBoundary('c67476988f320ca04d61815bcfd14360');
        $mp->addPart(new Stato_Mailer_Mime_Part('test'));
        $mp->addPart(new Stato_Mailer_Mime_Part('<b>test</b>', 'text/html'));
        $this->assertEquals($str, (string) $mp);
    }
    
    public function testMultipartTree()
    {
        $str = <<<EOT
Content-Type: multipart/mixed; boundary="c67476988f320ca04d61815bcfd14361"

This is a multi-part message in MIME format.
--c67476988f320ca04d61815bcfd14361
Content-Type: multipart/alternative; boundary="c67476988f320ca04d61815bcfd14360"

This is a multi-part message in MIME format.
--c67476988f320ca04d61815bcfd14360
Content-Type: text/plain; charset="UTF-8"
Content-Transfer-Encoding: 8bit

test
--c67476988f320ca04d61815bcfd14360
Content-Type: text/html; charset="UTF-8"
Content-Transfer-Encoding: 8bit

<b>test</b>
--c67476988f320ca04d61815bcfd14360--
--c67476988f320ca04d61815bcfd14361
Content-Type: image/png; name="hello.png"
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="hello.png"

iVBORw0KGgoAAAANSUhEUgAAAAYAAAAFCAYAAABmWJ3mAAAAAXNSR0IArs4c6QAAAAZiS0dE
AP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAIBJREFUCNc9yK8OQWEcx+HPiyCb
JtjciKaZILgARTRXwfQTXIVGM03xZwLZtHN2zpiZ2fu+v6/miY+b7EYyRcwMJMo4us0+lWgB
WcQJEPRaA9qNDqxuS12Ks8bboTb3tSQpRC+e34ckKf9k/0z2U5WS04y3f1Gr1jEZi8Oca3rk
B3WXTGfs7Y8kAAAAAElFTkSuQmCC
--c67476988f320ca04d61815bcfd14361--
EOT;
        $mp1 = new Stato_Mailer_Mime_Multipart();
        $mp1->setBoundary('c67476988f320ca04d61815bcfd14360');
        $mp1->addPart(new Stato_Mailer_Mime_Part('test'));
        $mp1->addPart(new Stato_Mailer_Mime_Part('<b>test</b>', 'text/html'));
        $mp2 = new Stato_Mailer_Mime_Multipart(Stato_Mailer_Mime_Multipart::MIXED);
        $mp2->setBoundary('c67476988f320ca04d61815bcfd14361');
        $mp2->addPart($mp1);
        $mp2->addPart(new Stato_Mailer_Mime_Attachment(fopen(dirname(__FILE__) . '/../files/image.png', 'r'), 'hello.png', 'image/png'));
        $this->assertEquals($str, (string) $mp2);
    }
}