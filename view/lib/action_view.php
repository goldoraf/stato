<?php

class SActionView
{
    private $assigns     = array();
    private $controller  = null;
    private $templateDir = null;
    
    public function __construct($controller)
    {
        $this->controller = $controller;
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
        if (!is_readable($template))
            throw new SException('Template not found : '.$template);
            
        $this->assigns = $assigns;
        $this->templateDir = dirname($template);
        
        $compiled = $this->compiledTemplatePath($template);
        
        if (!$this->isCompiledTemplate($template, $compiled))
            $this->compile($template, $compiled);
        
        ob_start();
        include ($compiled);
        $str = ob_get_contents();
        ob_end_clean();
        
        return $str;
    }
    
    public function renderPartial($partialPath, $localAssigns = Null)
    {
        list($path, $partial) = $this->partialPieces($partialPath);
        $template = "$path/_$partial.php";
        
        if ($localAssigns == Null)
            $localAssigns = array($partial => $this->assigns[$partial]);
        
        $view = new SActionView($this->controller);
        return $view->render($template, $localAssigns);
    }
    
    public function renderPartialCollection($partialPath, $collection, $spacerTemplate = Null)
    {
        list($path, $partial) = $this->partialPieces($partialPath);
        $template = "$path/_$partial.php";
        
        $partialsCollec = array();
        $counterName = $partial.'_counter';
        $counter = 1;
        foreach($collection as $element)
        {
            $localAssigns[$counterName] = $counter;
            $localAssigns[$partial] = $element;
            $view = new SActionView($this->controller);
            $partialsCollec[] = $view->render($template, $localAssigns);
            $counter++;
        }
        return implode('', $partialsCollec);
    }
    
    public function cacheStart($id, $lifetime = 30)
    {
        if ($this->isFragmentCacheValid($id, $lifetime))
        {
            echo file_get_contents($this->fragmentCachePath($id));
            return true;
        }
        else
        {
            ob_start();
            //ob_implicit_flush(false); necessary ?
            return false;
        }
    }
    
    public function cacheEnd($id = null)
    {
        $str = ob_get_contents();
        ob_end_clean();
        file_put_contents($this->fragmentCachePath($id), 'Fragment cached !'.$str.'Fragment cached !');
        echo $str;
    }
    
    private function compile($template, $compiledPath)
    {
        $content  = file_get_contents($template);
        $compiled = preg_replace(array('/(<\?=\s)/i', '/(<\?\s)/i', '/(<\%=\s)/i', '/(<\%\s)/i'),
                                 array('<?php echo ', '<?php '), $content);
        
        file_put_contents($compiledPath, $compiled);
        return $compiledPath;
    }
    
    private function isCompiledTemplate($template, $compiledPath)
    {
        if (!file_exists($compiledPath)) return false;
        if (filemtime($compiledPath) < filemtime($template))
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
    
    private function partialPieces($partialPath)
    {
        if (strpos($partialPath, '/') === false)
            return array($this->templateDir, $partialPath);
        else
        {
            list($subPath, $partial) = explode('/', $partialPath);
            return array(APP_DIR."/views/$subPath", $partial);
        }
    }
    
    private function isFragmentCacheValid($id, $lifetime)
    {
        if (file_exists($this->fragmentCachePath($id))) return true;
        return false;
    }
    
    private function fragmentCachePath($id)
    {
        return ROOT_DIR."/cache/fragments/{$id}";
    }
}

?>
