<?php

class SSimpleBackend extends SAbstractBackend
{
    protected $initialized = array();
    
    protected $translations = array();
    
    protected function lookup($locale, $key)
    {
        if (!$this->is_initialized($locale)) $this->init_translations($locale);
        if (array_key_exists($key, $this->translations[$locale]))
            return $this->translations[$locale][$key];
            
        return $key;
    }
    
    protected function init_translations($locale)
    {
        $this->load_translations(SI18n::get_data_paths(), $locale);
        $this->initialized[] = $locale;
    }
    
    protected function is_initialized($locale)
    {
        return in_array($locale, $this->initialized);
    }
    
    protected function load_translations($paths, $locale)
    {
        $this->translations[$locale] = array();
        
        foreach ($paths as $path) {
            $file = $this->get_translation_file_path($path, $locale);
            if (file_exists($file)) {
                $translations = $this->load_translation_file($file);
                $this->translations[$locale] 
                    = array_merge($this->translations[$locale], $translations);
            }
        }
    }
    
    protected function load_translation_file($file)
    {
        return include($file);
    }
    
    protected function get_translation_file_path($path, $locale)
    {
        return $path.'/'.$locale.'.php';
    }
}