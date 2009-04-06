<?php

class SYamlBackend extends SSimpleBackend
{
    protected function load_translation_file($file)
    {
        if (!function_exists('syck_load'))
            throw new SI18nException('Syck extension is not installed');
            
        return syck_load(file_get_contents($file));
    }
    
    protected function get_translation_file_path($path, $locale)
    {
        return $path.'/'.$locale.'.yml';
    }
}