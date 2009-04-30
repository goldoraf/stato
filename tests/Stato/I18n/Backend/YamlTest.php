<?php

namespace Stato\I18n\Backend;

use Stato\I18n\I18n;
use Stato\TestCase;

require_once __DIR__ . '/../../TestsHelper.php';

class YamlTest extends TestCase
{
    public function setup()
    {
        if (!extension_loaded('syck'))
            $this->markTestSkipped('The Syck extension is not available');
             
        I18n::addDataPath(__DIR__ . '/../data/yaml');
        $this->backend = new Yaml();
    }
    
    public function testTranslate()
    {
        $this->assertEquals('Stato est un cadre de travail PHP5.', 
            $this->backend->translate('fr', 'Stato is a PHP5 framework.'));
    }
    
    public function testTranslateAndInterpolate()
    {
        $this->assertEquals("La date d'aujourd'hui est 31/07/2007", 
            $this->backend->translate('fr', "Today's date is %date%", array('%date%' => '31/07/2007')));
        $this->assertEquals("La date d'aujourd'hui est 31/07/2007", 
            $this->backend->translate('fr', "Today's date is %date%", array('date' => '31/07/2007')));
    }
    
    public function testTranslatef()
    {
        $this->assertEquals('Le champ IP est requis.', 
            $this->backend->translatef('fr', '%s is required.', array('IP')));
    }
    
    public function testTranslateAndPluralize()
    {
        $this->assertEquals('pas de message', $this->backend->translateAndPluralize('fr', 'inbox', 0));
        $this->assertEquals('1 message', $this->backend->translateAndPluralize('fr', 'inbox', 1));
        $this->assertEquals('2 messages', $this->backend->translateAndPluralize('fr', 'inbox', 2));
        $this->assertEquals('3 messages', $this->backend->translateAndPluralize('fr', 'inbox', 3));
    }
}