<?php

require_once dirname(__FILE__) . '/../../../test/tests_helper.php';

require_once dirname(__FILE__) . '/../webservice.php';

class XmlRpcServerTest extends StatoTestCase
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
        $request = $server->parse_request($xml);
        $this->assertEquals('test', $request->service);
        $this->assertEquals('myMethod', $request->method);
        $this->assertEquals(array('hello world', true, 12), $request->casted_params);
    }
}
