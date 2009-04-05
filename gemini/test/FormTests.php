<?php

require_once dirname(__FILE__) . '/../../test/tests_helper.php';

require_once 'form/FormInputTest.php';
require_once 'form/FormFieldTest.php';
require_once 'form/FormErrorsTest.php';
require_once 'form/FormTest.php';

class FormTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Stato webflow form');
        $suite->addTestSuite('SFormInputTest');
        $suite->addTestSuite('SFormFieldTest');
        $suite->addTestSuite('SFormErrorsTest');
        $suite->addTestSuite('SFormTest');
        return $suite;
    }
}
