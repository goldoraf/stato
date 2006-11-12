<?php

require_once(CORE_DIR.'/webservice/webservice.php');

class XmlRpcClientTest extends XmlTestCase
{
    public function test_request_to_xml()
    {
        $req = new SXmlRpcRequest('test.myMethod', array('hello world', true, 12));
        $xml = <<<EOD
<?xml version="1.0"?>
<methodCall>
<methodName>test.myMethod</methodName>
<params>
<param><value><string>hello world</string></value></param>
<param><value><boolean>1</boolean></value></param>
<param><value><int>12</int></value></param>
</params>
</methodCall>
EOD;
        $this->assertDomEqual($xml, $req->to_xml());
    }
    
    public function test_client()
    {
        $client = new SXmlRpcClient('http://xmlrpc-c.sourceforge.net/api/sample.php');
        //$this->assertEqual(array('sum' => 8, 'difference' => 2), $client->sample->sumAndDifference(5, 3));
        //print_r($client->system->methodSignature('sample.sumAndDifference'));
    }
}

?>
