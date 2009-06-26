<?php

namespace Stato\I18n\Backend;

use Stato\I18n\I18n;
use Stato\TestCase;

require_once __DIR__ . '/../../TestsHelper.php';

class YamlTest extends SimpleTest
{
    public function setup()
    {
        if (!extension_loaded('syck'))
            $this->markTestSkipped('The Syck extension is not available');
             
        $this->backend = new Yaml(__DIR__ . '/../data/yaml');
    }
    
    public function tearDown()
    {
        @unlink(__DIR__ . '/../tmp/klingon.yml');
        @unlink(__DIR__ . '/../tmp/fr.yml');
    }
    
    public function testSave()
    {
        $this->backend->store('klingon', 'The Klingon culture is a very ancient one, though there is no record of its roots.', 
                                         'tIQqu\' tlhIngan Segh tIgh je, \'ach mungDaj qonlu\'be\'.');
        $this->backend->save('klingon', __DIR__ . '/../tmp');
        
        $backend = new Yaml(__DIR__ . '/../tmp');
        $this->assertEquals('tIQqu\' tlhIngan Segh tIgh je, \'ach mungDaj qonlu\'be\'.',
            $backend->translate('klingon', 'The Klingon culture is a very ancient one, though there is no record of its roots.'));
    }
    
    public function testSaveWithExistentTranslations()
    {
        $this->backend->store('fr', 'hello world', 'bonjour le monde', 'foo_controller.php:10');
        $this->backend->save('fr', __DIR__ . '/../tmp');
        $backend = new Yaml(__DIR__ . '/../tmp');
        $this->assertEquals('bonjour le monde', $backend->translate('fr', 'hello world'));
        $this->assertEquals('Stato est un cadre de travail PHP5.', $backend->translate('fr', 'Stato is a PHP5 framework.'));
    }
}