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
    private $values;
    
    public function __construct($template, $values)
    {
        $this->template = $template;
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
}

?>
