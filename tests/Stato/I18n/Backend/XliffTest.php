<?php






require_once dirname(__FILE__) . '/../../TestsHelper.php';

class Stato_I18n_Backend_XliffTest extends Stato_I18n_Backend_SimpleTest
{
    public function setup()
    {
        $this->backend = new Stato_I18n_Backend_Xliff(dirname(__FILE__) . '/../data/xliff');
    }
    
    public function tearDown()
    {
        @unlink(dirname(__FILE__) . '/../tmp/klingon.xml');
        @unlink(dirname(__FILE__) . '/../tmp/fr.xml');
    }
    
    public function testSave()
    {
        $this->backend->store('klingon', 'The Klingon culture is a very ancient one, though there is no record of its roots.', 
                                         'tIQqu\' tlhIngan Segh tIgh je, \'ach mungDaj qonlu\'be\'.');
        $this->backend->save('klingon', dirname(__FILE__) . '/../tmp');
        
        $backend = new Stato_I18n_Backend_Xliff(dirname(__FILE__) . '/../tmp');
        $this->assertEquals('tIQqu\' tlhIngan Segh tIgh je, \'ach mungDaj qonlu\'be\'.',
            $backend->translate('klingon', 'The Klingon culture is a very ancient one, though there is no record of its roots.'));
    }
    
    public function testSaveWithExistentTranslations()
    {
        $this->backend->store('fr', 'hello world', 'bonjour le monde', 'foo_controller.php:10');
        $this->backend->save('fr', dirname(__FILE__) . '/../tmp');
        $backend = new Stato_I18n_Backend_Xliff(dirname(__FILE__) . '/../tmp');
        $this->assertEquals('bonjour le monde', $backend->translate('fr', 'hello world'));
        $this->assertEquals('Stato est un cadre de travail PHP5.', $backend->translate('fr', 'Stato is a PHP5 framework.'));
        $this->assertEquals('2 messages', $backend->translateAndPluralize('fr', 'inbox', 2));
    }
}