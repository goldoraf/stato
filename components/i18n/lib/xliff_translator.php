<?php

class SXliffTranslator implements SITranslator
{
    public static $data_path = null;
    
    protected $cache = array();
    
    public function __construct()
    {
        
    }
    
    public function fetch($key, SLocale $locale, $plural_number = null)
    {
        $language = $locale->code;
        
        if (!isset($this->cache[$language])) $this->cache_language_data($language);
        
        if (isset($this->cache[$language][$key]))
        {
            if (!is_array($this->cache[$language][$key])) return $this->cache[$language][$key];
            if (is_int($plural_number))
            {
                $c = $plural_number;
                $n = eval($locale->plural_rule);
                return $this->cache[$language][$key][$n];
            }
            return $this->cache[$language][$key][0];
        }
        return $key;
    }
    
    private function cache_language_data($language)
    {
        if (self::$data_path !== null) $path = self::$data_path.'/';
        else $path = STATO_APP_PATH.'/i18n/';
        $path.= $language.'.xml';
        
        if (file_exists($path)) $this->cache[$language] = $this->parse_data($path);
        else $this->cache[$language] = array();
    }
    
    private function parse_data($path)
    {
        $xml = simplexml_load_file($path);
        $translations = array();
        
        foreach ($xml->xpath("/xliff/file/body/trans-unit") as $unit)
        {
            $source = (string) $unit->source;
            $translations[$source] = (string) $unit->target;
        }
        
        foreach ($xml->xpath("/xliff/file/body/group[@restype='x-gettext-plurals']") as $group)
        {
            $group_sources = array();
            $group_translations = array();
            foreach ($group->{'trans-unit'} as $unit)
            {
                $group_sources[] = (string) $unit->source;
                $group_translations[] = (string) $unit->target;
            }
            foreach ($group_sources as $source) $translations[$source] = $group_translations;
        }
        
        return $translations;
    }
}

?>
