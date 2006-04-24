<?php

require_once(CORE_DIR.'/webservice/webservice.php');

class HttpClientTest extends UnitTestCase
{
    public function testGetRequest()
    {
        $client = new SHttpClient('http://www.php.net/');
        $response = $client->get();
        $this->assertEqual(200, $response->code);
        $this->assertNotNull($response->body);
    }
    
    public function testXmlPostRequest()
    {
        $xml = '<?xml version="1.0"?>
        <methodCall>
            <methodName>sample.sumAndDifference</methodName>
            <params>
                <param><value><i4>5</i4></value></param>
                <param><value><i4>3</i4></value></param>
            </params>
       </methodCall>';
        $client = new SHttpClient('http://xmlrpc-c.sourceforge.net/api/sample.php', array('Content-Type: text/xml'));
        $response = $client->post($xml);
        $this->assertFalse(strpos($response->body, '<value><int>8</int></value>') === false);
    }
}

?>
