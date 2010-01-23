<?php






require_once dirname(__FILE__) . '/../TestsHelper.php';

class Stato_I18n_IntlIntegrationTest extends Stato_TestCase
{
    public function setup()
    {
        if (!extension_loaded('intl'))
            $this->markTestSkipped('The intl extension is not available');
    }
    
    public function testSetLocaleFromHttpAccept()
    {
        $httpAccept = 'fr-fr,fr;q=0.8,en-us;q=0.6,en;q=0.4,de;q=0.2';
        Stato_I18n_I18n::setLocale(Locale::acceptFromHttp($httpAccept));
        $this->assertEquals('fr_FR', Stato_I18n_I18n::getLocale());
    }
}

