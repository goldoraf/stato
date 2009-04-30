<?php

namespace Stato\I18n;

use Stato\TestCase;
use Locale;

require_once __DIR__ . '/../TestsHelper.php';

class IntlIntegrationTest extends TestCase
{
    public function setup()
    {
        if (!extension_loaded('intl'))
            $this->markTestSkipped('The intl extension is not available');
    }
    
    public function testSetLocaleFromHttpAccept()
    {
        $httpAccept = 'fr-fr,fr;q=0.8,en-us;q=0.6,en;q=0.4,de;q=0.2';
        I18n::setLocale(Locale::acceptFromHttp($httpAccept));
        $this->assertEquals('fr_FR', I18n::getLocale());
    }
}

