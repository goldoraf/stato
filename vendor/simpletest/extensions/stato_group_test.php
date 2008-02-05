<?php

class StatoGroupTest extends GroupTest
{
    public function addTestFolder($path)
    {
        set_include_path(get_include_path().PATH_SEPARATOR.$path);
        $it = new DirectoryIterator($path);
        foreach ($it as $elt) if ($elt->isFile()) 
            $this->addTestFile($elt->getFilename());
    }
}

?>
