<?php

class LocaleTest extends UnitTestCase
{
    public function test_basic()
    {
        SLocale::set('fr_FR');
        $this->assertEqual('français', SLocale::active()->language);
        $this->assertEqual('France', SLocale::active()->country);
        
        SLocale::set('en_US');
        $this->assertEqual('English', SLocale::active()->language);
        $this->assertEqual('United States', SLocale::active()->country);
    }
    
    public function test_translator()
    {
        SLocale::set('fr_FR');
        SXliffTranslator::$data_path = STATO_CORE_PATH.'/components/i18n/test/data';
        $this->assertEqual('Stato est un cadre de travail PHP5.', 
            SLocale::translate('Stato is a PHP5 framework.'));
        $this->assertEqual("La date d'aujourd'hui est 31/07/2007", 
            SLocale::translate("Today's date is %s", array("31/07/2007")));
        $this->assertEqual("1 fichier supprimé", 
            SLocale::translate("%d file deleted", array(1)));
        $this->assertEqual("1 fichier supprimé", 
            SLocale::translate("%d files deleted", array(1)));
        $this->assertEqual("2 fichiers supprimés", 
            SLocale::translate("%d file deleted", array(2)));
        $this->assertEqual("2 fichiers supprimés", 
            SLocale::translate("%d files deleted", array(2)));
        $this->assertEqual("3 fichiers supprimés", 
            SLocale::translate("%d file deleted", array(3)));
        $this->assertEqual("3 fichiers supprimés", 
            SLocale::translate("%d files deleted", array(3)));
        $this->assertEqual("1 fichier supprimé", 
            SLocale::translate(array("%d file deleted", "%d files deleted"), array(1)));
        $this->assertEqual("2 fichiers supprimés", 
            SLocale::translate(array("%d file deleted", "%d files deleted"), array(2)));
        $this->assertEqual("3 fichiers supprimés", 
            SLocale::translate(array("%d file deleted", "%d files deleted"), array(3)));
        
        SLocale::set('pl_PL');
        $this->assertEqual("1 plik", SLocale::translate("%d file", array(1)));
        $this->assertEqual("2 pliki", SLocale::translate("%d file", array(2)));
        $this->assertEqual("3 pliki", SLocale::translate("%d file", array(3)));
        $this->assertEqual("4 pliki", SLocale::translate("%d file", array(4)));
        $this->assertEqual("5 pliko'w", SLocale::translate("%d file", array(5)));
        $this->assertEqual("12 pliko'w", SLocale::translate("%d file", array(12)));
        $this->assertEqual("21 pliko'w", SLocale::translate("%d file", array(21)));
        $this->assertEqual("22 pliki", SLocale::translate("%d file", array(22)));
        $this->assertEqual("25 pliko'w", SLocale::translate("%d file", array(25)));
        
        $this->assertEqual("1 plik", SLocale::translate("%d files", array(1)));
        $this->assertEqual("4 pliki", SLocale::translate("%d files", array(4)));
        $this->assertEqual("21 pliko'w", SLocale::translate("%d files", array(21)));
        
        $this->assertEqual("1 plik", SLocale::translate(array("%d file", "%d files"), array(1)));
        $this->assertEqual("4 pliki", SLocale::translate(array("%d file", "%d files"), array(4)));
        $this->assertEqual("21 pliko'w", SLocale::translate(array("%d file", "%d files"), array(21)));
    }
}

?>
