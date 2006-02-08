<?php

class SRenderer
{
    private $template;
    private $compiled;
    private $values;
    
    public function __construct($template, $values)
    {
        $this->template = $template;
        $this->compiled = $this->compiledTemplatePath($template);
        $this->values = $values;
    }
    
    public function __get($name)
    {
        if (isset($this->values[$name])) return $this->values[$name];
    }
    
    public function __set($name, $value)
    {
        throw new SException('You\'re not allowed to reassign template variables !');
    }
    
    public function render()
    {
        if (!is_readable($this->template))
        {
            throw new SException('Template not found : '.$this->template);
        }
        
        if (!$this->isCompiledTemplate()) $this->compile();
        
        ob_start ();
        include ($this->compiled);
        $str = ob_get_contents();
        ob_end_clean();
        
        return $str;
    }
    
    private function compile()
    {
        $template = file_get_contents($this->template);
        $compiled = preg_replace(array('/(<\?= )/i', '/(<\? )/i', '/(<\%= )/i', '/(<\% )/i'),
                                 array('<?php echo ', '<?php '), $template);
        file_put_contents($this->compiled, $compiled);
    }
    
    private function isCompiledTemplate()
    {
        if (!file_exists($this->compiled)) return false;
        if (filemtime($this->compiled) != filemtime($this->template))
        {
            unlink($this->compiled);
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
