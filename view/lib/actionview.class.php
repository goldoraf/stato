<?php

class SActionView
{
    private $assigns = array();
    
    public function __construct()
    {
        
    }
    
    public function __get($name)
    {
        if (isset($this->assigns[$name])) return $this->assigns[$name];
    }
    
    public function __set($name, $value)
    {
        throw new SException('You\'re not allowed to reassign template variables !');
    }
    
    public function render($template, $assigns)
    {
        $this->assigns = $assigns;
        
        if (!is_readable($template))
            throw new SException('Template not found : '.$template);
        
        if (!$this->isCompiledTemplate($template))
            $compiled = $this->compile($template);
        
        ob_start();
        include ($compiled);
        $str = ob_get_contents();
        ob_end_clean();
        
        return $str;
    }
    
    private function compile($template)
    {
        $content  = file_get_contents($template);
        $compiled = preg_replace(array('/(<\?= )/i', '/(<\? )/i', '/(<\%= )/i', '/(<\% )/i'),
                                 array('<?php echo ', '<?php '), $content);
        $compiledPath = $this->compiledTemplatePath($template);
        file_put_contents($compiledPath, $compiled);
        return $compiledPath;
    }
    
    private function isCompiledTemplate($template)
    {
        $compiledPath = $this->compiledTemplatePath($template);
        if (!file_exists($compiledPath)) return false;
        if (filemtime($compiledPath) != filemtime($template))
        {
            unlink($compiledPath);
            return false;
        }
        return true;
    }
    
    private function compiledTemplatePath($template)
    {
        return ROOT_DIR.'/cache/templates/'.md5($template);
    }
}

?>
