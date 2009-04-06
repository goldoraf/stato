<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'i18n.php';
require_once 'backend/abstract.php';
require_once 'backend/yaml.php';

class Stato_I18n_YamlBackendTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        if (!extension_loaded('syck'))
            $this->markTestSkipped('The Syck extension is not available');
             
        Stato_I18n::addDataPath(dirname(__FILE__).'/data/yaml');
        $this->backend = new Stato_I18n_YamlBackend();
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