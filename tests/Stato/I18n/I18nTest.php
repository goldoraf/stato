<?php





require_once dirname(__FILE__) . '/../TestsHelper.php';

class Stato_I18n_I18nTest extends Stato_TestCase
{
    public function setup()
    {
        Stato_I18n_I18n::addDataPath(dirname(__FILE__) . '/data/simple');
        Stato_I18n_I18n::setLocale('fr');
    }
    
    public function testShouldDefaultToSimpleBackend()
    {
        $this->assertEquals('Stato_I18n_Backend_Simple', get_class(Stato_I18n_I18n::getBackend()));
    }
    
    public function testDefaultLocale()
    {
        $this->assertEquals('en', Stato_I18n_I18n::getDefaultLocale());
    }
    
    public function testTranslate()
    {
        $this->assertEquals('Stato est un cadre de travail PHP5.', 
            Stato_I18n_I18n::translate('Stato is a PHP5 framework.'));
        $this->assertEquals('Stato est un cadre de travail PHP5.', 
            __('Stato is a PHP5 framework.'));
    }
    
    public function testTranslateAndInterpolate()
    {
        $this->assertEquals("La date d'aujourd'hui est 31/07/2007", 
            Stato_I18n_I18n::translate("Today's date is %date%", array('date' => '31/07/2007')));
        $this->assertEquals("La date d'aujourd'hui est 31/07/2007", 
            __("Today's date is %date%", array('date' => '31/07/2007')));
    }
    
    public function testTranslatef()
    {
        $this->assertEquals('Le champ IP est requis.', 
            Stato_I18n_I18n::translatef('%s is required.', array('IP')));
        $this->assertEquals('Le champ IP est requis.', 
            _f('%s is required.', array('IP')));
    }
    
    public function testTranslateAndPluralize()
    {
        $this->assertEquals('2 messages', Stato_I18n_I18n::translateAndPluralize('inbox', 2));
        $this->assertEquals('2 messages', _p('inbox', 2));
    }
}