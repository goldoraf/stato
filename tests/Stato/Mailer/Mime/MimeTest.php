<?php

namespace Stato\Mailer\Mime;

use Stato\TestCase;

require_once __DIR__ . '/../../TestsHelper.php';

class MimeTest extends TestCase
{
    public function testIsPrintable()
    {
        $this->assertTrue(Mime::isPrintable('simple text'));
    }
    
    public function testIsNotPrintable()
    {
        $this->assertFalse(Mime::isPrintable('not so simple text éà&ç'));
    }
    
    public function testBase64Encode()
    {
        $str = 'not so simple text éà&ç';
        $this->assertEquals($str, base64_decode(Mime::encodeBase64($str)));
    }
    
    public function testBase64EncodeImageResource()
    {
        $encoded = <<<EOT
iVBORw0KGgoAAAANSUhEUgAAAAYAAAAFCAYAAABmWJ3mAAAAAXNSR0IArs4c6QAAAAZiS0dE
AP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAIBJREFUCNc9yK8OQWEcx+HPiyCb
JtjciKaZILgARTRXwfQTXIVGM03xZwLZtHN2zpiZ2fu+v6/miY+b7EYyRcwMJMo4us0+lWgB
WcQJEPRaA9qNDqxuS12Ks8bboTb3tSQpRC+e34ckKf9k/0z2U5WS04y3f1Gr1jEZi8Oca3rk
B3WXTGfs7Y8kAAAAAElFTkSuQmCC
EOT;
        $this->assertEquals($encoded, Mime::encode(fopen(__DIR__. '/../files/image.png', 'r'), Mime::BASE64));
    }
    
    public function testBase64EncodeImageContent()
    {
        $encoded = <<<EOT
iVBORw0KGgoAAAANSUhEUgAAAAYAAAAFCAYAAABmWJ3mAAAAAXNSR0IArs4c6QAAAAZiS0dE
AP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAIBJREFUCNc9yK8OQWEcx+HPiyCb
JtjciKaZILgARTRXwfQTXIVGM03xZwLZtHN2zpiZ2fu+v6/miY+b7EYyRcwMJMo4us0+lWgB
WcQJEPRaA9qNDqxuS12Ks8bboTb3tSQpRC+e34ckKf9k/0z2U5WS04y3f1Gr1jEZi8Oca3rk
B3WXTGfs7Y8kAAAAAElFTkSuQmCC
EOT;
        $this->assertEquals($encoded, Mime::encode(file_get_contents(__DIR__. '/../files/image.png'), Mime::BASE64));
    }
    
    public function testEncodeQuotedPrintable()
    {
        $html = file_get_contents(__DIR__. '/../files/dummy.html');
        $this->assertEquals($html, quoted_printable_decode(Mime::encodeQuotedPrintable($html)));
    }
    
    public function testEncodeQuotedPrintableStream()
    {
        $html = file_get_contents(__DIR__. '/../files/dummy.html');
        $this->assertEquals($html, quoted_printable_decode(
            Mime::encodeStream(fopen(__DIR__. '/../files/dummy.html', 'r'), Mime::QUOTED_PRINTABLE)));
    }
    
    public function test8bitEncoding()
    {
        $this->assertEquals('test', Mime::encode('test', '8bit'));
    }
    
    public function test8bitStreamEncoding()
    {
        $html = file_get_contents(__DIR__. '/../files/dummy.html');
        $this->assertEquals($html, quoted_printable_decode(
            Mime::encode(fopen(__DIR__. '/../files/dummy.html', 'r'), '8bit')));
    }
    
    public function testNotSupportedEncodingShouldThrow()
    {
        $this->setExpectedException('Stato\Mailer\Mime\Exception');
        Mime::encode('test', 'dummy');
    }
    
    public function testNotSupportedEncodingShouldThrowWithStreamsToo()
    {
        $this->setExpectedException('Stato\Mailer\Mime\Exception');
        Mime::encodeStream('test', 'dummy');
    }
}