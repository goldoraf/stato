<?php

define('ROOT_DIR', str_replace('\\', '/', realpath(dirname(__FILE__).'/../..')));
define('CORE_DIR', ROOT_DIR.'/core');
define('APP_DIR', ROOT_DIR.'/app');

require_once(CORE_DIR.'/common/lib/inflection.php');

$dir = new RecursiveDirectoryIterator(CORE_DIR);
iterate_files($dir, array('build'));

function iterate_files($dir, $exceptions)
{
    $files_to_exclude = array('inflection.php', 'dir.php', 'colortextreporter.php', 
    'helpertestcase.php', 'showpasses.php', 'statotestcase.php', 'run_tests.php', 'test_underscore.php');
    
    foreach ($dir as $file)
    {
        if ($file->hasChildren() && !in_array((string) $file, $exceptions))
        {
            echo "Opening $file\n";
            iterate_files($file->getChildren(), $exceptions);
        }
        elseif (substr($file, -4) == '.php' && !in_array((string) $file, $files_to_exclude))
        {
            $path = $dir->getPath()."/$file";
            echo "Processing $path\n";
            underscore_file($path);
        }
    }
}

function underscore_file($class_file)
{
    $regexes = array
    (
        '/(\$[a-zA-Z0-9]+)/i',
        '/(->[a-zA-Z0-9]+)/i',
        '/(function [a-zA-Z0-9]+)/i',
        '/(::[a-zA-Z0-9]+)/i'
    );
    
    $code = file_get_contents($class_file);
    $new_code = preg_replace_callback($regexes, 'underscore', $code);
    
    file_put_contents($class_file, $new_code);
}

function underscore($matches)
{
    $exceptions = array('->assertEqual', '->assertNotEqual', '->assertDomEqual', '->assertException', '->assertNull', '->assertTrue', '->assertFalse', 
    '->assertIsA', 'function offsetExists', 'function offsetSet', 'function offsetGet', 'function offsetUnset', 'function __toString', 
    '->__toString', '$_POST', '$_GET', '$_FILES', '$_SERVER', '::UnitTestCase', '::CSV_MODE', '::INI_MODE');
    
    if (!in_array($matches[1], $exceptions)) return SInflection::underscore($matches[1]);
    else return $matches[1];
}

//underscore_file(ROOT_DIR.'/core/model/test/active_record.test.php');

?>
