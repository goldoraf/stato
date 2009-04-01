<?php

require_once dirname(__FILE__) . '/../../../test/tests_helper.php';

require_once 'HttpClientTest.php';
require_once 'XmlRpcClientTest.php';
require_once 'XmlRpcInvocationTest.php';
require_once 'XmlRpcServerTest.php';

class StatoWebserviceAllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Stato Webservice');
        $suite->addTestSuite('HttpClientTest');
        $suite->addTestSuite('XmlRpcClientTest');
        $suite->addTestSuite('XmlRpcInvocationTest');
        $suite->addTestSuite('XmlRpcServerTest');
        return $suite;
    }
}
