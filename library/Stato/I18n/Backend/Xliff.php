<?php



class Stato_I18n_Backend_Xliff extends Stato_I18n_Backend_Simple
{
    public function save($locale, $path)
    {
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('xliff');
        $xml->writeAttribute('version', '1.0');
        $xml->startElement('file');
        $xml->writeAttribute('original', 'global');
        $xml->writeAttribute('source-language', 'en');
        $xml->writeAttribute('target-language', $locale);
        $xml->writeAttribute('datatype', 'plaintext');
        $xml->startElement('body');
        
        $count = 1;
        foreach ($this->translations[$locale] as $key => $translation) {
            if (is_array($translation)) {
                $count2 = 0;
                $xml->startElement('group');
                $xml->writeAttribute('restype', 'x-gettext-plurals');
                foreach ($translation as $k => $v) {
                    $xml->startElement('trans-unit');
                    $xml->writeAttribute('id', "{$count}[{$count2}]");
                    if (is_string($k)) $xml->writeAttribute('resname', $k);
                    $xml->writeElement('source', $key);
                    $xml->writeElement('target', $v);
                    $xml->endElement();
                    $count2++;
                }
                $xml->endElement();
            } else {
                $xml->startElement('trans-unit');
                $xml->writeAttribute('id', $count);
                $xml->writeElement('source', $key);
                $xml->writeElement('target', $translation);
                $xml->endElement();
            }
            $count++;
        }
        
        $xml->endElement();
        $xml->endElement();
        $xml->endElement();
        file_put_contents($this->getTranslationFilePath($path, $locale), $xml->flush());
    }
    
    protected function loadTranslationFile($file)
    {
        $xml = simplexml_load_file($file);
        $translations = array();
        
        foreach ($xml->xpath("/xliff/file/body/trans-unit") as $unit) {
            $source = (string) $unit->source;
            $translations[$source] = (string) $unit->target;
        }
        
        foreach ($xml->xpath("/xliff/file/body/group[@restype='x-gettext-plurals']") as $group) {
            $groupSources = array();
            $groupTranslations = array();
            foreach ($group->{'trans-unit'} as $unit) {
                $groupSources[] = (string) $unit->source;
                $resname = (string) $unit['resname'];
                if (!empty($resname))
                    $groupTranslations[$resname] = (string) $unit->target;
                else
                    $groupTranslations[] = (string) $unit->target;
            }
            foreach ($groupSources as $source) $translations[$source] = $groupTranslations;
        }
        
        return $translations;
    }
    
    protected function getTranslationFilePath($path, $locale)
    {
        return $path.'/'.$locale.'.xml';
    }
}