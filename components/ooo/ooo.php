<?php

class OOo
{
    private static $text_ns    = "urn:oasis:names:tc:opendocument:xmlns:text:1.0";
    private static $draw_ns    = "urn:oasis:names:tc:opendocument:xmlns:drawing:1.0";
    private static $svg_ns     = "urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0";
    private static $office_ns  = "urn:oasis:names:tc:opendocument:xmlns:office:1.0";
    
    public static function mail_merge($data_source, $form_letter_path, $new_filename)
    {
        $mask = self::mask_content($form_letter_path);
        $doc  = DOMDocument::loadXml($mask);
        $page = $doc->getElementsByTagNameNS(self::$office_ns, 'text')->item(0);
        
        $blank_text = self::create_blank_document($mask);
        $clones     = array();
        
        foreach ($data_source as $values)
        {
            $clone = $page->cloneNode(true);
            self::replace_values($clone, $values);
            $clones[] = $clone;
        }
        
        foreach ($clones as $clone)
        {
            foreach ($clone->childNodes as $node)
            {
                if ($node->nodeName != 'draw:frame' && $node->nodeName != 'draw:rect')
                    $blank_text->appendChild($blank_text->ownerDocument->importNode($node, true));
            }
        }
        
        return self::zip_document($form_letter_path, $blank_text->ownerDocument->saveXml(), $new_filename);
    }
    
    private static function mask_content($mask_path)
    {
        return file_get_contents($mask_path."/content.xml");
    }
    
    private static function replace_values($element, $values)
    {
        foreach ($element->getElementsByTagNameNS(self::$text_ns, 'database-display') as $node)
        {
            $field = $node->getAttributeNS(self::$text_ns, 'column-name');
            if (is_array($values) && isset($values[$field])) $node->nodeValue = $values[$field];
            else $node->nodeValue = $values->$field;
        }
    }
    
    private static function zip_document($mask_path, $content, $new_filename)
    {
        $zip = new ZipArchive();
        
        if ($zip->open($new_filename, ZIPARCHIVE::CREATE) !== true)
            throw new Exception("Cannot open zip file: $new_filename");
        
        $zip->addFromString("content.xml", $content);
        
        $dir = new DirectoryIterator($mask_path);
        foreach ($dir as $file)
        {
            if ($file->isDot() || $file->getFilename() == '.svn') continue;
            if ($file->isFile() && $file->getFilename() != 'content.xml')
                $zip->addFile($mask_path.'/'.$file->getFilename(), $file->getFilename());
            if ($file->isDir())
                self::add_dir_to_zip($zip, $mask_path, $file->getFilename());
        }
        unset($dir);
        
        if (!$zip->close())
            throw new Exception("Cannot close zip file: $new_filename");
    }
    
    private static function add_dir_to_zip($zip, $mask_path, $dir)
    {
        $files = SDir::entries("$mask_path/$dir");
        $zip->addEmptyDir($dir);
        foreach ($files as $file) $zip->addFile("$mask_path/$dir/$file", "$dir/$file"); 
    }
    
    private static function create_blank_document($mask)
    {
        $doc = DOMDocument::loadXml($mask);
        $body = $doc->getElementsByTagNameNS(self::$office_ns, 'body')->item(0);
        $body->removeChild($body->firstChild);
        $temp = $doc->createElementNS(self::$office_ns, 'text');
        return $body->appendChild($temp);
    }
}

?>