<?php

require_once dirname(__FILE__) . '/../../test/TestsHelper.php';

require_once 'i18n.php';
require_once 'backend/abstract.php';
require_once 'backend/yaml.php';

class SYamlBackendTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        if (!extension_loaded('syck'))
            $this->mark_test_skipped('The Syck extension is not available');
             
        $this->backend = new SYamlBackend(dirname(__FILE__).'/data/yaml');
    }
    
    public function test_translate()
    {
        $this->assertEquals('Stato est un cadre de travail PHP5.', 
            $this->backend->translate('fr', 'Stato is a PHP5 framework.'));
    }
    
    public function test_translate_and_interpolate()
    {
        $this->assertEquals("La date d'aujourd'hui est 31/07/2007", 
            $this->backend->translate('fr', "Today's date is %date%", array('%date%' => '31/07/2007')));
        $this->assertEquals("La date d'aujourd'hui est 31/07/2007", 
            $this->backend->translate('fr', "Today's date is %date%", array('date' => '31/07/2007')));
    }
    
    public function test_translatef()
    {
        $this->assertEquals('Le champ IP est requis.', 
            $this->backend->translatef('fr', '%s is required.', array('IP')));
    }
    
    public function test_translate_and_pluralize()
    {
        $this->assertEquals('pas de message', $this->backend->translate_and_pluralize('fr', 'inbox', 0));
        $this->assertEquals('1 message', $this->backend->translate_and_pluralize('fr', 'inbox', 1));
        $this->assertEquals('2 messages', $this->backend->translate_and_pluralize('fr', 'inbox', 2));
        $this->assertEquals('3 messages', $this->backend->translate_and_pluralize('fr', 'inbox', 3));
    }
}