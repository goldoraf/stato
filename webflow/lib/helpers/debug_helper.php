<?php

function debug_view()
{
    $str = link_to_function('Debug', "Toggle.display('debug-view'); Toggle.display(this)", 
                            array('style' => 'position:fixed; top:0px; right:0px', 'id' => 'show-debug-view'));
    $str.= '<div id="debug-view" style="overflow:scroll; display:none; position:fixed; top:0; right:0; width:50%; height:50%; background-color:#ccc; border:1px solid #000; font-size:11px;">';
    $str.= link_to_function('Hide', "Toggle.display('debug-view'); Toggle.display('show-debug-view')");
    $str.= '<strong>SQL used :</strong><br /><ul>';
    foreach (SActiveRecord::connection()->get_log() as $sql) $str.= '<li>'.$sql.'</li>';
    $str.= '</ul><strong>Session</strong><br />';
    $str.= nl2br(str_replace( " ","&nbsp;", print_r($_SESSION, true)));
    $str.= '</div>';
    return $str;
}

?>
