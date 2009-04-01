<?php

require_once dirname(__FILE__) . '/../../test/tests_helper.php';

require_once 'helpers/AjaxHelperTest.php';
require_once 'helpers/AssetTagHelperTest.php';
require_once 'helpers/DateHelperTest.php';
require_once 'helpers/FormHelperTest.php';
require_once 'helpers/FormOptionsTest.php';
require_once 'helpers/FormTagHelperTest.php';
require_once 'helpers/NumberHelperTest.php';
require_once 'helpers/TagHelperTest.php';

class HelpersTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Stato Gemini Helpers');
        $suite->addTestSuite('AjaxHelperTest');
        $suite->addTestSuite('AssetTagHelperTest');
        $suite->addTestSuite('DateHelperTest');
        $suite->addTestSuite('FormHelperTest');
        $suite->addTestSuite('FormOptionsTest');
        $suite->addTestSuite('FormTagHelperTest');
        $suite->addTestSuite('NumberHelperTest');
        $suite->addTestSuite('TagHelperTest');
        return $suite;
    }
}
