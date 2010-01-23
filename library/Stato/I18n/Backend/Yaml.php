<?php





class Stato_I18n_Backend_Yaml extends Stato_I18n_Backend_Simple
{
    public function save($locale, $path)
    {
        file_put_contents($this->getTranslationFilePath($path, $locale), 
                          syck_dump($this->translations[$locale]));
    }
    
    protected function loadTranslationFile($file)
    {
        if (!function_exists('syck_load'))
            throw new Stato_I18n_Exception('Syck extension is not installed');
            
        return syck_load(file_get_contents($file));
    }
    
    protected function getTranslationFilePath($path, $locale)
    {
        return $path.'/'.$locale.'.yml';
    }
}