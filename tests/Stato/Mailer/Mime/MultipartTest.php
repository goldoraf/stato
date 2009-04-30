<?php

namespace Stato\Mailer\Mime;

use Stato\TestCase;

require_once __DIR__ . '/../../TestsHelper.php';

class MultipartTest extends TestCase
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
        $mp = new Multipart();
        $mp->setBoundary('c67476988f320ca04d61815bcfd14360');
        $mp->addPart(new Part('test'));
        $mp->addPart(new Part('<b>test</b>', 'text/html'));
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
        $mp1 = new Multipart();
        $mp1->setBoundary('c67476988f320ca04d61815bcfd14360');
        $mp1->addPart(new Part('test'));
        $mp1->addPart(new Part('<b>test</b>', 'text/html'));
        $mp2 = new Multipart(Multipart::MIXED);
        $mp2->setBoundary('c67476988f320ca04d61815bcfd14361');
        $mp2->addPart($mp1);
        $mp2->addPart(new Attachment(fopen(__DIR__ . '/../files/image.png', 'r'), 'hello.png', 'image/png'));
        $this->assertEquals($str, (string) $mp2);
    }
}