<?php

$class_name = array_shift($SCRIPT_ARGS);

if ($class_name === null)
    die('Please specify a name for your model...');

if (strpos($class_name, '/') !== false)
    list($subdir, $class_name) = explode('/', $class_name);
    
$content = SCodeGenerator::generate_class($class_name, '    public static $objects;', 'SActiveRecord');
$file = SInflection::underscore($class_name).'.php';
if (!empty($subdir)) $file = $subdir.'/'.$file;
$path = $WWW_ROOT.'/'$APP_NAME.'/app/models/'.$file;
if (file_exists($path))
{
    echo "WARNING : file $path already exists !\n"
    .'Do you want to overwrite (o), or abort (a) ? ';
    $answer = fgetc(STDIN);
    if ($answer == 'a') die("\nFile generation aborted.\n");
}
file_put_contents($path, $content);

?>
