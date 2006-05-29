<?php

define('ROOT_DIR', str_replace('\\', '/', realpath(dirname(__FILE__).'/../..')));

define('CORE_DIR', ROOT_DIR.'/core');
require_once(CORE_DIR.'/common/common.php');
require_once(CORE_DIR.'/cli/cli.php');

if ($_SERVER['argc'] == 3)
{
    switch ($_SERVER['argv'][1])
    {
        case 'model':
            if (strpos($_SERVER['argv'][2], '/') !== false)
                list($subdir, $assigns['className']) = explode('/', $_SERVER['argv'][2]);
            else
                $assigns['className'] = $_SERVER['argv'][2];
                
            $content = SCodeGenerator::renderTemplate(CORE_DIR.'/cli/lib/templates/model.txt', $assigns);
            $file = SInflection::underscore($assigns['className']).'.php';
            if (!empty($subdir)) $file = $subdir.'/'.$file;
            $path = ROOT_DIR.'/app/models/'.$file;
            if (file_exists($path))
            {
                echo "WARNING : file $path already exists !\n"
                .'Do you want to overwrite (o), update (u) or abort (a) ? ';
                $answer = fgetc(STDIN);
                if ($answer == 'a')
                {
                    echo "\nFile generation aborted.\n";
                    die();
                }
                elseif ($answer == 'u')
                    $content = SCodeGenerator::insertCode(file_get_contents($path), $content);
                else
                    $content = SCodeGenerator::generateClass($assigns['className'], $content);
            }
            else
            {
                $content = SCodeGenerator::generateClass($assigns['className'], $content, 'SActiveRecord');
            }
            file_put_contents($path, $content);
            break;
    }
}
else
{
    echo 'This script requires 2 arguments.\n';
    die();
}

?>
