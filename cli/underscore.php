<?php

define('ROOT_DIR', str_replace('\\', '/', realpath(dirname(__FILE__).'/../..')));
define('CORE_DIR', ROOT_DIR.'/core');
define('APP_DIR', ROOT_DIR.'/app');

require_once(CORE_DIR.'/common/lib/inflection.php');

$dir = new RecursiveDirectoryIterator(CORE_DIR);
iterate_files($dir, array('build'));

function iterate_files($dir, $exceptions)
{
    foreach ($dir as $file)
    {
        if ($file->hasChildren() && !in_array((string) $file, $exceptions))
        {
            echo "Opening $file\n";
            iterate_files($file->getChildren(), $exceptions);
        }
        elseif (substr($file, -4) == '.php')
        {
            $path = $dir->getPath()."/$file";
            echo "Processing $path\n";
            underscore($path);
        }
    }
}

function underscore($class_file)
{
    $exceptions = array('assertEqual', 'assertNull', 'assertTrue', 'assertFalse', 'offsetExists', 
    'offsetSet', 'offsetGet', 'offsetUnset', '__toString');
    
    $code = file_get_contents($class_file);
    $new_code = '';
    $function_opened = false;
    $method_or_prop = false;
    $tokens = token_get_all($code);
    foreach ($tokens as $token)
    {
        if (is_string($token)) $new_code.= $token;
        else
        {
            list($id, $text) = $token;
            if ($id == T_FUNCTION)
            {
                $function_opened = true;
                $new_code.= $text;
            }
            elseif ($function_opened && $id == T_STRING)
            {
                $function_opened = false;
                $new_code.= SInflection::underscore($text);
            }
            elseif ($id == T_OBJECT_OPERATOR || $id == T_PAAMAYIM_NEKUDOTAYIM)
            {
                $method_or_prop = true;
                $new_code.= $text;
            }
            elseif ($method_or_prop && $id == T_STRING)
            {
                $method_or_prop = false;
                if (!in_array($text, $exceptions) $new_code.= SInflection::underscore($text);
                else $new_code.= $text;
            }
            elseif ($id == T_VARIABLE) $new_code.= SInflection::underscore($text);
            else $new_code.= $text;
        }
    }
    
    file_put_contents($class_file, $new_code);
}

?>
