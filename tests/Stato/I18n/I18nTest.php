<?php

namespace Stato\I18n;

use Stato\TestCase;

require_once __DIR__ . '/../TestsHelper.php';

class I18nTest extends TestCase
{
    public function setup()
    {
        I18n::addDataPath(__DIR__ . '/data/simple');
        I18n::setLocale('fr');
    }
    
    public function testShouldDefaultToSimpleBackend()
    {
        $this->assertEquals('Stato\I18n\Backend\Simple', get_class(I18n::getBackend()));
    }
    
    public function testDefaultLocale()
    {
        $this->assertEquals('en', I18n::getDefaultLocale());
    }
    
    public function testTranslate()
    {
        $this->assertEquals('Stato est un cadre de travail PHP5.', 
            I18n::translate('Stato is a PHP5 framework.'));
        $this->assertEquals('Stato est un cadre de travail PHP5.', 
            __('Stato is a PHP5 framework.'));
    }
    
    public function testTranslateAndInterpolate()
    {
        $this->assertEquals("La date d'aujourd'hui est 31/07/2007", 
            I18n::translate("Today's date is %date%", array('date' => '31/07/2007')));
        $this->assertEquals("La date d'aujourd'hui est 31/07/2007", 
            __("Today's date is %date%", array('date' => '31/07/2007')));
    }
    
    public function testTranslatef()
    {
        $this->assertEquals('Le champ IP est requis.', 
            I18n::translatef('%s is required.', array('IP')));
        $this->assertEquals('Le champ IP est requis.', 
            _f('%s is required.', array('IP')));
    }
    
    public function testTranslateAndPluralize()
    {
        $this->assertEquals('2 messages', I18n::translateAndPluralize('inbox', 2));
        $this->assertEquals('2 messages', _p('inbox', 2));
    }
}