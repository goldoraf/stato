<?php

class SJavascriptGenerator
{
    private $lines = array();
    
    public function __construct()
    {
    
    }
    
    public function insert_html($position, $id, $content)
    {
        return $this->js_call("new Insertion.{$position}", array($id, $this->escape_content($content)));
    }
    
    public function replace_html($id, $content)
    {
        return $this->js_call('Element.update', array($id, $this->escape_content($content)));
    }
    
    private function js_call($function, $args)
    {
        return javascript_tag("try {\n"."$function (".implode(', ', $this->args_for_js($args)).")"
        ."\n} catch (e) { alert('JS error :\\n\\n' + e.toString()); }"); 
    }
    
    private function escape_content($content)
    {
        return preg_replace('/\r\n|\n|\r/', '\\n', addslashes($content));
    }
    
    private function args_for_js($args)
    {
        foreach ($args as $k => $v) $args[$k] = "\"$v\"";
        return $args;
    }
}

?>
