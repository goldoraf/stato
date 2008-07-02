<?php

class StatoGroupTest extends TestSuite
{
    public function addTestFolder($path)
    {
        set_include_path(get_include_path().PATH_SEPARATOR.$path);
        
        $it = new DirectoryIterator($path);
        foreach ($it as $elt) if ($elt->isFile()) 
            $this->addFile($elt->getFilename());
    }
    
    public function addTestFile($path)
    {
        $this->_label.= ': '.basename($path, '.php');
        set_include_path(get_include_path().PATH_SEPARATOR.dirname($path));
        $this->addFile(basename($path));
    }
}

?>
