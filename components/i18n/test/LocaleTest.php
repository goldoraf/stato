<?php

require_once dirname(__FILE__) . '/../../../test/tests_helper.php';

require_once dirname(__FILE__) . '/../i18n.php';

class LocaleTest extends PHPUnit_Framework_TestCase
{
    public function test_basic()
    {
        SLocale::set('fr_FR');
        $this->assertEquals('français', SLocale::active()->language);
        $this->assertEquals('France', SLocale::active()->country);
        
        SLocale::set('en_US');
        $this->assertEquals('English', SLocale::active()->language);
        $this->assertEquals('United States', SLocale::active()->country);
    }
    
    public function test_translator()
    {
        SLocale::set('fr_FR');
        SXliffTranslator::$data_path = STATO_CORE_PATH.'/components/i18n/test/data';
        $this->assertEquals('Stato est un cadre de travail PHP5.', 
            SLocale::translate('Stato is a PHP5 framework.'));
        $this->assertEquals("La date d'aujourd'hui est 31/07/2007", 
            SLocale::translate("Today's date is %s", array("31/07/2007")));
        $this->assertEquals("1 fichier supprimé", 
            SLocale::translate("%d file deleted", array(1)));
        $this->assertEquals("1 fichier supprimé", 
            SLocale::translate("%d files deleted", array(1)));
        $this->assertEquals("2 fichiers supprimés", 
            SLocale::translate("%d file deleted", array(2)));
        $this->assertEquals("2 fichiers supprimés", 
            SLocale::translate("%d files deleted", array(2)));
        $this->assertEquals("3 fichiers supprimés", 
            SLocale::translate("%d file deleted", array(3)));
        $this->assertEquals("3 fichiers supprimés", 
            SLocale::translate("%d files deleted", array(3)));
        $this->assertEquals("1 fichier supprimé", 
            SLocale::translate(array("%d file deleted", "%d files deleted"), array(1)));
        $this->assertEquals("2 fichiers supprimés", 
            SLocale::translate(array("%d file deleted", "%d files deleted"), array(2)));
        $this->assertEquals("3 fichiers supprimés", 
            SLocale::translate(array("%d file deleted", "%d files deleted"), array(3)));
        
        SLocale::set('pl_PL');
        $this->assertEquals("1 plik", SLocale::translate("%d file", array(1)));
        $this->assertEquals("2 pliki", SLocale::translate("%d file", array(2)));
        $this->assertEquals("3 pliki", SLocale::translate("%d file", array(3)));
        $this->assertEquals("4 pliki", SLocale::translate("%d file", array(4)));
        $this->assertEquals("5 pliko'w", SLocale::translate("%d file", array(5)));
        $this->assertEquals("12 pliko'w", SLocale::translate("%d file", array(12)));
        $this->assertEquals("21 pliko'w", SLocale::translate("%d file", array(21)));
        $this->assertEquals("22 pliki", SLocale::translate("%d file", array(22)));
        $this->assertEquals("25 pliko'w", SLocale::translate("%d file", array(25)));
        
        $this->assertEquals("1 plik", SLocale::translate("%d files", array(1)));
        $this->assertEquals("4 pliki", SLocale::translate("%d files", array(4)));
        $this->assertEquals("21 pliko'w", SLocale::translate("%d files", array(21)));
        
        $this->assertEquals("1 plik", SLocale::translate(array("%d file", "%d files"), array(1)));
        $this->assertEquals("4 pliki", SLocale::translate(array("%d file", "%d files"), array(4)));
        $this->assertEquals("21 pliko'w", SLocale::translate(array("%d file", "%d files"), array(21)));
    }
}

