<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'form/FormInputTest.php';
require_once 'form/FormFieldTest.php';
require_once 'form/FormErrorsTest.php';
require_once 'form/FormTest.php';

class Stato_Webflow_FormTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Stato webflow form');
        $suite->addTestSuite('Stato_FormInputTest');
        $suite->addTestSuite('Stato_FormFieldTest');
        $suite->addTestSuite('Stato_FormErrorsTest');
        $suite->addTestSuite('Stato_FormTest');
        return $suite;
    }
}
