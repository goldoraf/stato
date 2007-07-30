<?php

class BuildI18nDataCommand extends SCommand
{
    protected $allowed_options = array('path' => true);
   
    public function execute()
    {
        if (!isset($this->options['path']))
            throw new SConsoleException("Please provide a path to the data.");
            
        $dir = new DirectoryIterator($this->options['path']);
        foreach ($dir as $file)
            if ($file->isFile()) $this->parse_data($file->getFilename());
    }
    
    private function save_to_file($orig_filename, $path, $data)
    {
        $str = SCodeGenerator::generate_file('return '.SCodeGenerator::array_to_string($data).";\n");
        $filename = substr($orig_filename, 0, -4).'.php';
        file_put_contents($path.'/'.$filename, $str);
    }
    
    private function parse_data($xml_file)
    {
        $data = array();
        $xml = simplexml_load_file($this->options['path'].'/'.$xml_file);
        
        $data['language_type'] = (string) $xml->identity->language['type'];
        
        if (isset($xml->identity->territory))
            $data['country_code'] = (string) $xml->identity->territory['type'];
        
        if (isset($xml->localeDisplayNames->languages))
            $this->save_to_file($xml_file, STATO_CORE_PATH.'/components/i18n/data/languages', $this->parse_languages_list($xml));
        if (isset($xml->localeDisplayNames->territories))
            $this->save_to_file($xml_file, STATO_CORE_PATH.'/components/i18n/data/countries', $this->parse_territories_list($xml));
        if (isset($xml->numbers->currencies) && count($xml->xpath("//currency/displayName")) != 0)
            $this->save_to_file($xml_file, STATO_CORE_PATH.'/components/i18n/data/currencies', $this->parse_currencies_list($xml));
            
        if (isset($xml->numbers->percentFormats->percentFormatLength->percentFormat->pattern))
            $data['percent_format'] = (string) $xml->numbers->percentFormats->percentFormatLength->percentFormat->pattern;
        if (isset($xml->numbers->currencyFormats->currencyFormatLength->currencyFormat->pattern))
            $data['currency_format'] = (string) $xml->numbers->currencyFormats->currencyFormatLength->currencyFormat->pattern;
            
        if (isset($xml->dates->calendars))
        {
            $cal_greg = $xml->xpath("//calendar[@type='gregorian']");
            foreach ($cal_greg[0]->dateFormats->dateFormatLength as $length)
                $data['date_formats'][(string) $length['type']] = (string) $length->dateFormat->pattern;
        }
        
        $languages = $this->reload_data($xml_file, 'languages');
        if (isset($languages[$data['language_type']])) $data['language'] = $languages[$data['language_type']];
        
        if (isset($data['country_code']))
        {
            $countries = $this->reload_data($xml_file, 'countries');
            if (isset($countries[$data['country_code']])) $data['country'] = $countries[$data['country_code']];
        }
        
        $this->save_to_file($xml_file, STATO_CORE_PATH.'/components/i18n/data', $data);
    }
    
    private function parse_languages_list($xml)
    {
        $languages = array();
        foreach ($xml->localeDisplayNames->languages->language as $language)
            if (!isset($language['draft']))
                $languages[(string) $language['type']] = (string) $language;
        return $languages;
    }
    
    private function parse_territories_list($xml)
    {
        $territories = array();
        foreach ($xml->localeDisplayNames->territories->territory as $territory)
            if (strlen((string) $territory['type']) == 2)
                $territories[(string) $territory['type']] = (string) $territory;
        return $territories;
    }
    
    private function parse_currencies_list($xml)
    {
        $currencies = array();
        foreach ($xml->numbers->currencies->currency as $currency)
            foreach ($currency->displayName as $name)
                if (!isset($name['draft']))
                    $currencies[(string) $currency['type']] = (string) $name;
        return $currencies;
    }
    
    private function reload_data($xml_file, $folder)
    {
        $path = STATO_CORE_PATH.'/components/i18n/data/'.$folder.'/';
        if (strpos($xml_file, '_') == false)
            $data = @include($path.substr($xml_file, 0, -4).'.php');
        else
        {
            list($lang, ) = explode('_', $xml_file);
            $data = @include($path.$lang.'.php');
            $path2 = $path.substr($xml_file, 0, -4).'.php';
            if (file_exists($path2))
                $data = array_merge($data, include($path2));
        }
        return $data;
    }
}

?>
