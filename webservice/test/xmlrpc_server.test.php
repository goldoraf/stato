<?php

require_once(STATO_CORE_PATH.'/webservice/webservice.php');

class XmlRpcServerTest extends XmlTestCase
{
    public function test_basic_request_parsing()
    {
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
        $server = new SXmlRpcServer();
        $this->assertEqual(array('test.myMethod', array('hello world', true, 12)),
                           $server->parse_request($xml));
    }
    
    /*public function test_server()
    {
        $client = new SXmlRpcClient('http://bdtef_v2/api/xmlrpc');
        echo $client->gestion->centres->hello_world('raphael', 'rougeron');
    }*/
}

?>
