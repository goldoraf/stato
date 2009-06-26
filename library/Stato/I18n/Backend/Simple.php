<?php

namespace Stato\I18n\Backend;

use Stato\I18n\I18n;
use Stato\I18n\Exception;

class Simple
{
    protected $dataPaths = array();
    protected $initialized = array();
    protected $translations = array();
    protected $comments = array();
    
    private static $pluralRules = array
    (
        '0' => array('hu','ja','ko','tr'),
        '$c == 1 ? 0 : 1' => array('da','nl','en','de','no','sv','et','fi','fr','el','he','it','pt','es','eo'),
        '$c == 1 ? 0 : ($c == 2 ? 1 : 2)' => array('ga','gd'),
        '($c%10 == 1 && $c%100 != 11) ? 0 : ($c%10 >= 2 && $c%10 <= 4 && ($c%100 < 10 || $c%100 >= 20) ? 1 : 2)' => array('hr','cs','ru','sk','uk'),
        '($c%10 == 1 && $c%100 != 11) ? 0 : ($c != 0 ? 1 : 2)' => array('lv'),
        '($c%10 == 1 && $c%100 != 11) ? 0 : ($c%10 >= 2 && ($c%100 < 10 || $c%100 >= 20) ? 1 : 2)' => array('lt'),
        '$c == 1 ? 0 : ($c%10 >= 2 && $c%10 <= 4 && ($c%100 < 10 || $c%100 >= 20) ? 1 : 2)' => array('pl'),
        '$c%100 == 1 ? 0 : ($c%100 == 2 ? 1 : ($c%100 == 3 || $c%100 == 4 ? 2 : 3))' => array('sl')
    );
    
    public function __construct($dataPaths)
    {
        if (!is_array($dataPaths)) $dataPaths = array($dataPaths);
        $this->dataPaths = $dataPaths;   
    }
    
    public function translate($locale, $key, $values = array())
    {
        $entry = $this->lookup($locale, $key);
        if (!empty($values)) $entry = $this->interpolate($locale, $entry, $values);
        return $entry;
    }
    
    public function translatef($locale, $key, $values = array())
    {
        $entry = $this->lookup($locale, $key);
        return vsprintf($entry, $values);
    }
    
    public function translateAndPluralize($locale, $key, $count = 0)
    {
        $entry = $this->lookup($locale, $key);
        return $this->pluralize($locale, $entry, $count);
    }
    
    public function addKey($locale, $key, $comment = null)
    {
        $translation = $this->lookup($locale, $key);
        $this->store($locale, $key, $translation, $comment);
    }
    
    public function store($locale, $key, $translation, $comment = null)
    {
        if (!$this->isInitialized($locale)) $this->initTranslations($locale);
        $this->translations[$locale][$key] = $translation;
        if (!is_null($comment)) $this->comments[$locale][$key] = $comment;
    }
    
    public function save($locale, $path)
    {
        $php = '';
        foreach ($this->translations[$locale] as $key => $translation) {
            $php.= "    '".addcslashes($key, "'")."' => ";
            if (is_array($translation)) {
                $php.= "array(\n";
                foreach ($translation as $k => $v) {
                    $php.= "        ";
                    if (is_string($k)) $php.= "'".addcslashes($k, "'")."' => ";
                    $php.= "'".addcslashes($v, "'")."',\n";
                }
                $php.= "    ),\n";
            } else {
                $php.= "'".addcslashes($translation, "'")."',\n";
            }
        }
        file_put_contents($this->getTranslationFilePath($path, $locale), 
                          "<?php\n\nreturn array(\n{$php});");
    }
    
    protected function interpolate($locale, $entry, $values)
    {
        $pValues = array();
        foreach ($values as $k => $v) {
            if (!preg_match('/%[a-zA-Z0-9_\-]+%/', $k)) $k = '%'.$k.'%';
            $pValues[$k] = $v;
        }
        return str_replace(array_keys($pValues), array_values($pValues), $entry);
    }
    
    protected function pluralize($locale, $entry, $c)
    {
        if (!is_array($entry)) return $entry;
        if ($c == 0 && array_key_exists('zero', $entry)) $key = 'zero';
        else $key = eval($this->getPluralRule($locale));
        
        if (!array_key_exists($key, $entry))
            throw new Exception('Invalid pluralization data: '.var_export($entry, true)."\n count: $c");
        
        return sprintf($entry[$key], $c);
    }
    
    protected function getPluralRule($locale)
    {
        foreach (self::$pluralRules as $rule => $locales)
            if (in_array($locale, $locales)) return 'return '.$rule.';';
    }
    
    protected function lookup($locale, $key)
    {
        if (!$this->isInitialized($locale)) $this->initTranslations($locale);
        if (array_key_exists($key, $this->translations[$locale]))
            return $this->translations[$locale][$key];
            
        return $key;
    }
    
    protected function initTranslations($locale)
    {
        $this->loadTranslations($locale);
        $this->initialized[] = $locale;
    }
    
    protected function isInitialized($locale)
    {
        return in_array($locale, $this->initialized);
    }
    
    protected function loadTranslations($locale)
    {
        $this->translations[$locale] = array();
        $this->comments[$locale] = array();
        
        foreach ($this->dataPaths as $path) {
            $file = $this->getTranslationFilePath($path, $locale);
            if (file_exists($file)) {
                $translations = $this->loadTranslationFile($file);
                $this->translations[$locale] 
                    = array_merge($this->translations[$locale], $translations);
            }
        }
    }
    
    protected function loadTranslationFile($file)
    {
        return include($file);
    }
    
    protected function getTranslationFilePath($path, $locale)
    {
        return $path.'/'.$locale.'.php';
    }
}