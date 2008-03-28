<?php

class MimeTypeTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        SMimeType::register("image/png", 'png');
        SMimeType::register("application/pdf", 'pdf');   
    }
    
    public function test_parse_without_q()
    {
        $accept = "text/xml,application/xhtml+xml,text/yaml,application/xml,text/html,image/png,text/plain,application/pdf,*/*";
        $this->assertEquals(SMimeType::parse($accept), array('html', 'xml', 'yaml', 'png', 'text', 'pdf', 'all'));
    }
    
    public function test_parse_with_q()
    {
        $accept = "text/xml,application/xhtml+xml,text/yaml; q=0.3,application/xml,text/html; q=0.8,image/png,text/plain; q=0.5,application/pdf,*/*; q=0.2";
        $this->assertEquals(SMimeType::parse($accept), array('html', 'xml', 'png', 'pdf', 'text', 'yaml', 'all'));
        // Looking at Rails tests, a true port would return array('html', 'xml', 'png', 'text', 'pdf', 'yaml', 'all')
        // but after consulting RFC2616, I don't think it is correct
    }
}

?>
