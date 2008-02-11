<?php

class StatoGroupTest extends TestSuite
{
    private $tests_already_added = false;
    
    public function __construct($label = false)
    {
        parent::__construct($label);
        if (($tests = $this->specificTestsToRun()) !== false)
        {
            $this->addTests($tests);
            $this->tests_already_added = true;
        }
    }
    
    public function addTestFolder($path)
    {
        if ($this->tests_already_added) return;
        
        set_include_path(get_include_path().PATH_SEPARATOR.$path);
        
        $it = new DirectoryIterator($path);
        foreach ($it as $elt) if ($elt->isFile()) 
            $this->addTestFile($elt->getFilename());
    }
    
    private function addTests($tests)
    {
        $this->_label.= ': '.implode(', ', $tests);
        
        $tests_path = realpath(dirname($_SERVER['SCRIPT_NAME'])).'/test';
        set_include_path(get_include_path().PATH_SEPARATOR.$tests_path);
        foreach ($tests as $test) $this->addTestFile($test.'.test.php');
    }

    private function specificTestsToRun()
    {
        $args = $_SERVER['argv'];
        array_shift($args); // skip file name
        if (count($args) == 0) return false;
        return $args;
    }
}

?>
