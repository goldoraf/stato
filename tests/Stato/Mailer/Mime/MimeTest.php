<?php





require_once dirname(__FILE__) . '/../../TestsHelper.php';

class Stato_Mailer_Mime_MimeTest extends Stato_TestCase
{
    public function testIsPrintable()
    {
        $this->assertTrue(Stato_Mailer_Mime_Mime::isPrintable('simple text'));
    }
    
    public function testIsNotPrintable()
    {
        $this->assertFalse(Stato_Mailer_Mime_Mime::isPrintable('not so simple text éà&ç'));
    }
    
    public function testBase64Encode()
    {
        $str = 'not so simple text éà&ç';
        $this->assertEquals($str, base64_decode(Stato_Mailer_Mime_Mime::encodeBase64($str)));
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
        $this->assertEquals($encoded, Stato_Mailer_Mime_Mime::encode(fopen(dirname(__FILE__). '/../files/image.png', 'r'), Stato_Mailer_Mime_Mime::BASE64));
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
        $this->assertEquals($encoded, Stato_Mailer_Mime_Mime::encode(file_get_contents(dirname(__FILE__). '/../files/image.png'), Stato_Mailer_Mime_Mime::BASE64));
    }
    
    public function testEncodeQuotedPrintable()
    {
        $html = file_get_contents(dirname(__FILE__). '/../files/dummy.html');
        $this->assertEquals($html, quoted_printable_decode(Stato_Mailer_Mime_Mime::encodeQuotedPrintable($html)));
    }
    
    public function testEncodeQuotedPrintableStream()
    {
        $html = file_get_contents(dirname(__FILE__). '/../files/dummy.html');
        $this->assertEquals($html, quoted_printable_decode(
            Stato_Mailer_Mime_Mime::encodeStream(fopen(dirname(__FILE__). '/../files/dummy.html', 'r'), Stato_Mailer_Mime_Mime::QUOTED_PRINTABLE)));
    }
    
    public function test8bitEncoding()
    {
        $this->assertEquals('test', Stato_Mailer_Mime_Mime::encode('test', '8bit'));
    }
    
    public function test8bitStreamEncoding()
    {
        $html = file_get_contents(dirname(__FILE__). '/../files/dummy.html');
        $this->assertEquals($html, quoted_printable_decode(
            Stato_Mailer_Mime_Mime::encode(fopen(dirname(__FILE__). '/../files/dummy.html', 'r'), '8bit')));
    }
    
    public function testNotSupportedEncodingShouldThrow()
    {
        $this->setExpectedException('Stato_Mailer_Mime_Exception');
        Stato_Mailer_Mime_Mime::encode('test', 'dummy');
    }
    
    public function testNotSupportedEncodingShouldThrowWithStreamsToo()
    {
        $this->setExpectedException('Stato_Mailer_Mime_Exception');
        Stato_Mailer_Mime_Mime::encodeStream('test', 'dummy');
    }
}