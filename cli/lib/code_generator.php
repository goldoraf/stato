<?php

class SCodeGenerator
{
    private static $startBlockDelimiter = '// --------- GENERATED CODE ------------------------------------------------';
    private static $endBlockDelimiter   = '// --------- END OF GENERATED CODE -----------------------------------------';
    
    public static function generateClass($name, $content, $extends = null)
    {
        return self::phpStart()."class $name".(($extends !== null) ? " extends $extends" : '')
        ."\n{\n$content\n}\n".self::phpStop();
    }
    
    public static function renderTemplate($template, $assigns)
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
    
    public static function insertCode($code, $codeToInsert)
    {
        $newCode = '';
        $curlyCount = 0;
        $tokens = token_get_all($code);
        for ($i = 0; $i < $count = count($tokens); $i++)
        {
            $token = $tokens[$i];
            if (is_string($token))
            {
                if ($token == '{') $curlyCount++;
                elseif ($token == '}')
                {
                    $curlyCount--;
                    if ($curlyCount == 0) $newCode.= "\n$codeToInsert";
                }
                $newCode.= $token;
            }
            else $newCode.= $token[1];
        }
        return $newCode;
    }
    
    private static function phpStart()
    {
        return "<?php\n\n";
    }
    
    private static function phpStop()
    {
        return "\n?>\n";
    }
}

?>
