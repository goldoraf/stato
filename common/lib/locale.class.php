<?php

class SLocale
{
    public static $language = 'en';
    private static $strings = array();
    
    public static function initialize()
    {
        foreach (self::getAcceptedLanguages() as $language)
        {
            if (file_exists(ROOT_DIR.'/core/common/locale/'.$language.'.php'))
            {
                self::$language = $language;
                break;
            }
        }
        
        self::loadStrings();
        
        putenv("LANG=".self::$language.""); 
        setlocale(LC_ALL, self::$language);
    }
    
    public static function translate($key)
    {
        if (isset(self::$strings[$key])) return self::$strings[$key];
        else return $key;
    }
    
    private static function loadStrings()
    {
        self::$strings = include(ROOT_DIR.'/core/common/locale/'.self::$language.'.php');
        
        $moduleI18nFile = MODULES_DIR.'/'.SContext::$request->module.'/i18n/'.self::$language.'.php';
        
        if (file_exists($moduleI18nFile))
            self::$strings = array_merge(self::$strings, include($moduleI18nFile));
    }
    
    private static function getAcceptedLanguages()
    {
        $langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $qcandidat = 0;
        $nblang = count($langs);
        
        for ($i=0; $i<$nblang; $i++)
        {
            for ($j=0; $j<count($langs); $j++)
            {
                $lang = trim($langs[$j]); // Supprime les espaces avant et après $lang
                // Lang est de la forme langue;q=valeur
                
                if (!strstr($lang, ';') && $qcandidat != 1)
                {
                    // Si la chaine ne contient pas de valeur de préférence q
                    $candidat = $lang;
                    $qcandidat = 1;
                    $indicecandidat = $j;
                }
                else
                {
                    // On récupère l'indice q
                    $q = ereg_replace('.*;q=(.*)', '\\1', $lang);
                    
                    if ($q > $qcandidat)
                    {
                        $candidat = ereg_replace('(.*);.*', '\\1', $lang); ;
                        $qcandidat = $q;
                        $indicecandidat = $j;
                    } 
                }
            }
            
            $resultat[$i] = $candidat;
            
            $qcandidat=0;
            // On supprime la valeur du tableau
            unset($langs[$indicecandidat]);   
            $langs = array_values($langs);
        }
        return $resultat;
    }
}

?>
