<?php

require_once dirname(__FILE__) . '/../../../test/tests_helper.php';

require_once dirname(__FILE__) . '/../webservice.php';

class HttpClientTest extends PHPUnit_Framework_TestCase
{
    public function test_get_request()
    {
        $client = new SHttpClient('http://www.php.net/');
        $response = $client->get();
        $this->assertEquals(200, $response->code);
        $this->assertNotNull($response->body);
    }
    
    public function test_xml_post_request()
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

