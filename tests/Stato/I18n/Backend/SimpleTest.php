<?php

namespace Stato\I18n\Backend;

use Stato\I18n\I18n;
use Stato\TestCase;

require_once __DIR__ . '/../../TestsHelper.php';

class SimpleTest extends TestCase
{
    public function setup()
    {
        $this->backend = new Simple(__DIR__ . '/../data/simple');
    }
    
    public function tearDown()
    {
        @unlink(__DIR__ . '/../tmp/klingon.php');
        @unlink(__DIR__ . '/../tmp/fr.php');
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
    
    public function testTranslateWithoutTranslation()
    {
        $this->assertEquals('hello world', $this->backend->translate('fr', 'hello world'));
    }
    
    public function testStore()
    {
        $this->assertEquals('hello world', $this->backend->translate('fr', 'hello world'));
        $this->backend->store('fr', 'hello world', 'bonjour le monde');
        $this->assertEquals('bonjour le monde', $this->backend->translate('fr', 'hello world'));
    }
    
    public function testSave()
    {
        $php = <<<EOT
<?php

return array(
    'The Klingon culture is a very ancient one, though there is no record of its roots.' => 'tIQqu\' tlhIngan Segh tIgh je, \'ach mungDaj qonlu\'be\'.',
);
EOT;
        $this->backend->store('klingon', 'The Klingon culture is a very ancient one, though there is no record of its roots.', 
                                         'tIQqu\' tlhIngan Segh tIgh je, \'ach mungDaj qonlu\'be\'.');
        $this->backend->save('klingon', __DIR__ . '/../tmp');
        $this->assertEquals($php, file_get_contents(__DIR__ . '/../tmp/klingon.php'));
    }
    
    public function testSaveWithExistentTranslations()
    {
        $php = <<<EOT
<?php

return array(
    'Stato is a PHP5 framework.' => 'Stato est un cadre de travail PHP5.',
    '%s is required.' => 'Le champ %s est requis.',
    'Today\'s date is %date%' => 'La date d\'aujourd\'hui est %date%',
    'inbox' => array(
        'zero' => 'pas de message',
        '1 message',
        '%d messages',
    ),
    //foo_controller.php:10
    'hello world' => 'bonjour le monde',
);
EOT;
        $this->backend->store('fr', 'hello world', 'bonjour le monde', 'foo_controller.php:10');
        $this->backend->save('fr', __DIR__ . '/../tmp');
        $this->assertEquals($php, file_get_contents(__DIR__ . '/../tmp/fr.php'));
    }
}