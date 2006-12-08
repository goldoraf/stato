<?php

class SCodeGenerator
{
    private static $start_block_delimiter = '// --------- GENERATED CODE ------------------------------------------------';
    private static $end_block_delimiter   = '// --------- END OF GENERATED CODE -----------------------------------------';
    
    public static function generate_class($name, $content, $extends = null)
    {
        return self::php_start()."class $name".(($extends !== null) ? " extends $extends" : '')
        ."\n{\n$content\n}\n".self::php_stop();
    }
    
    public static function generate_file($content)
    {
        return self::php_start().$content.self::php_stop();
    }
    
    public static function render_template($template, $assigns = array())
    {
        if (!is_readable($template))
            throw new SException('Template not found : '.$template);
            
        extract($assigns);
            
        ob_start();
        include ($template);
        $str = ob_get_contents();
        ob_end_clean();
        
        return $str;
    }
    
    public static function insert_code($code, $code_to_insert)
    {
        $new_code = '';
        $curly_count = 0;
        $tokens = token_get_all($code);
        for ($i = 0; $i < $count = count($tokens); $i++)
        {
            $token = $tokens[$i];
            if (is_string($token))
            {
                if ($token == '{') $curly_count++;
                elseif ($token == '}')
                {
                    $curly_count--;
                    if ($curly_count == 0) $new_code.= "\n$code_to_insert";
                }
                $new_code.= $token;
            }
            else $new_code.= $token[1];
        }
        return $new_code;
    }
    
    private static function php_start()
    {
        return "<?php\n\n";
    }
    
    private static function php_stop()
    {
        return "\n?>\n";
    }
}

?>
