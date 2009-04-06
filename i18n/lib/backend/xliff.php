<?php

class SXliffBackend extends SSimpleBackend
{
    protected function load_translation_file($file)
    {
        $xml = simplexml_load_file($file);
        $translations = array();
        
        foreach ($xml->xpath("/xliff/file/body/trans-unit") as $unit) {
            $source = (string) $unit->source;
            $translations[$source] = (string) $unit->target;
        }
        
        foreach ($xml->xpath("/xliff/file/body/group[@restype='x-gettext-plurals']") as $group) {
            $group_sources = array();
            $group_translations = array();
            foreach ($group->{'trans-unit'} as $unit) {
                $group_sources[] = (string) $unit->source;
                $resname = (string) $unit['resname'];
                if (!empty($resname))
                    $group_translations[$resname] = (string) $unit->target;
                else
                    $group_translations[] = (string) $unit->target;
            }
            foreach ($group_sources as $source) $translations[$source] = $group_translations;
        }
        
        return $translations;
    }
    
    protected function get_translation_file_path($path, $locale)
    {
        return $path.'/'.$locale.'.xml';
    }
}