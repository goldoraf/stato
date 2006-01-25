<?php

/**
 * Renderer
 * 
 * classe de rendu de pages web.
 * elle utilise des templates PHP mais peut etre etendue 
 * pour chaque type de moteur de template.
 * 
 * @package 
 * @author goldoraf
 * @copyright Copyright (c) 2004
 * @version 0.4
 * @access public
 **/
class Renderer
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
    
    /**
     * Renderer::render()
     * 
     * retourne le rendu du template sous forme de chaine.
     * 
     * @return string
     **/
    public function render()
    {
        if (!$this->isCompiledTemplate()) $this->compile();
        
        extract ($this->values);
        
        if (!is_readable($this->template))
        {
            throw new Exception('Template not found : '.$this->template);
        } 
        
        ob_start ();
        include ($this->template);
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
