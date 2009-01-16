<?php

class Stato_MissingTemplate extends Exception {}

class Stato_View
{
    private $templatePaths = array();
    
    public function __construct()
    {
        
    }
    
    public function addPath($path)
    {
        $this->templatePaths[] = $path;
    }
    
    public function assign($assigns = array())
    {
        foreach ($assigns as $k => $v) $this->$k = $v;
    }
    
    public function render($template, $options = array())
    {
        $defaultOptions = array('locals' => array(), 'layout' => false);
        $options = array_merge($defaultOptions, $options);
        
        if ($options['layout'] !== false)
            return $this->renderWithLayout($template, $options);
            
        if (isset($options['collection']))
            return $this->renderCollection($template, $options);
            
        $templatePath = $this->templatePath($template);
        
        if (!is_readable($templatePath))
            throw new Stato_MissingTemplate($templatePath);
        
        extract($options['locals']);
        ob_start();
        include ($templatePath);
        return ob_get_clean();
    }
    
    private function renderWithLayout($template, $options)
    {
        $layout = 'layouts/'.$options['layout'];
        unset($options['layout']);
        $this->contentForLayout = $this->render($template, $options);
        return $this->render($layout, $options);
    }
    
    private function renderCollection($template, $options)
    {
        $partials = array();
        $elementName = basename($template, '.php');
        $counterName = "{$elementName}_counter";
        $counter = 1;
        foreach($options['collection'] as $element)
        {
            $locals[$counterName] = $counter;
            $locals[$elementName] = $element;
            $partials[] = $this->render($template, array('locals' => $locals));
            $counter++;
        }
        
        if (isset($options['spacer'])) $spacer = $options['spacer'];
        elseif (isset($options['spacer_template'])) $spacer = $this->render($options['spacer_template']);
        else $spacer = '';

        return implode($spacer, $partials);
    }
    
    private function templatePath($template)
    {
        if (file_exists($template)) return $template;
        foreach ($this->templatePaths as $path) {
            $possiblePath = "{$path}/{$template}.php";
            if (file_exists($possiblePath)) return $possiblePath;
        }
        throw new Stato_MissingTemplate($template);
    }
}