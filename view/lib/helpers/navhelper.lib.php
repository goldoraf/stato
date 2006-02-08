<?php

function simple_menu($links, $class='menu')
{
    if (empty($links)) return;
    $list = '';
    foreach ($links as $content => $urlOptions)
    {
        $list.= "<li>".link_to($content, $urlOptions)."</li>";
    }
    return "<ul class=\"{$class}\">{$list}</ul>";
}

function module_nav($module=Null, $class='menu', $except=array())
{
    if ($module == Null) $module = SContext::$request->module;
    if ($module == 'root') $folder = new SFolder(APP_DIR.'/controllers');
    else $folder = new SFolder(APP_DIR.'/modules/'.$module.'/controllers');
    
    $except[] = 'application';
    $controllers = array();
    foreach($folder as $file)
    {
        $controller = str_replace('controller.class.php', '', $file);
        if (!in_array($controller, $except))
            $controllers[ucfirst($controller)] = array('module' => $module, 'controller' => $controller, 'action' => 'index');
    } 
    return simple_menu($controllers, $class);
}

?>
