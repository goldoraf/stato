<?php

function directory_tree_for_select(RecursiveDirectoryIterator $dir, $relative_path = '', $level = 0)
{
    $tree = '';
    for ($dir->rewind(); $dir->valid(); $dir->next())
    {
        $file = $dir->getFilename();
        
        if ($dir->isDot() || $dir->isFile() || $file == '.svn') continue;

        if ($dir->isDir())
        {
            $tree.= content_tag('option', str_repeat('&nbsp;', $level*4).$file, array('value' => $relative_path.'/'.$file));
            if ($dir->hasChildren()) 
                $tree.= directory_tree_for_select($dir->getChildren(), $relative_path.'/'.$file, $level + 1);
        }
    }
    
    return $tree;
}

function file_css_class($filename)
{
    static $classes = array
    (
        'img-type'   => array('jpg', 'gif', 'png'),
        'audio-type' => array('mp3', 'ogg', 'wav'),
        'doc-type'   => array('doc', 'odt'),
        'xls-type'   => array('xls', 'odc'),
        'ppt-type'   => array('ppt'),
        'pdf-type'   => array('pdf'),
        'swf-type'   => array('swf')
    );
    
    if (strrpos($filename, '.') == strlen($filename) - 4) 
    {
        $ext = substr($filename, -3);
        foreach ($classes as $class => $types)
            if (in_array($ext, $types)) return $class;
    }
    
    return 'no-type';
}

?>
