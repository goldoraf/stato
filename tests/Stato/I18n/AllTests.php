<?php





require_once dirname(__FILE__) . '/../TestsHelper.php';

class Stato_I18n_AllTests
{
    public static function suite()
    {
        $suite = new Stato_TestSuite('Stato i18n');
        $suite->addTestSuite('Stato_I18n_I18nTest');
        $suite->addTestSuite('Stato_I18n_IntlIntegrationTest');
        $suite->addTestSuite('Stato_I18n_Backend_SimpleTest');
        $suite->addTestSuite('Stato_I18n_Backend_YamlTest');
        $suite->addTestSuite('Stato_I18n_Backend_XliffTest');
        return $suite;
    }
}