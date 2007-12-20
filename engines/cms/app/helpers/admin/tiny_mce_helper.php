<?php

function tiny_mce_init($text_area_id)
{
    return javascript_tag(
        'tinyMCE.init({
            theme : "advanced",
            mode: "exact",
            elements : "'.$text_area_id.'",
            plugins : "inlinepopups",
            theme_advanced_toolbar_location : "top",
            theme_advanced_buttons1 : "bold,italic,underline,strikethrough,separator,"
            + "justifyleft,justifycenter,justifyright,justifyfull,formatselect,"
            + "bullist,numlist,outdent,indent",
            theme_advanced_buttons2 : "link,unlink,anchor,image,separator,"
            +"undo,redo,cleanup,code,separator,sub,sup,charmap",
            theme_advanced_buttons3 : "",
            theme_advanced_blockformats : "p,h2,h3,h4,blockquote,pre,adress",
            theme_advanced_link_styles : "Lien vers un PDF=file pdf-type; Lien vers un doc Word=file doc-type; Lien vers un Powerpoint=file ppt-type; Lien vers un site externe=external-link",
            height:"350px",
            width:"600px",
            file_browser_callback : "fileBrowser",
            relative_urls : false
          });
          
          function fileBrowser (field_name, url, type, win) {
            var fileBrowserWindow = new Array();
            fileBrowserWindow["title"] = "File Browser";
            if (type == "file") {
                fileBrowserWindow["file"] = "'.url_for(array('controller' => 'admin/pages', 'action' => 'browse')).'";
            } else {
                fileBrowserWindow["file"] = "'.url_for(array('controller' => 'admin/files', 'action' => 'browse')).'";
            }
            fileBrowserWindow["close_previous"] = "no";
            fileBrowserWindow["width"] = "420";
            fileBrowserWindow["height"] = "400";
            tinyMCE.openWindow(fileBrowserWindow, { window : win, input : field_name, resizable : "yes", inline : "yes" });
            return false;
          }'
    );
}

?>
