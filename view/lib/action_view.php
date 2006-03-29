<?php

class SActionView
{
    private $assigns     = array();
    private $controller  = null;
    private $templateDir = null;
    private $tmpCacheKey = null;
    
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
    
    public function cacheStart($id = null, $lifetime = 30)
    {
        if (!$this->controller->performCaching) return;
        
        if ($id === null) $id = array('controller' => $this->controller->controllerPath(),
                                      'action' => $this->controller->actionName());
        
        $cacheKey = $this->fragmentCacheKey($id);
        if ($this->isFragmentCacheValid($cacheKey, $lifetime))
        {
            echo file_get_contents($cacheKey);
            return true;
        }
        $this->tmpCacheKey = $cacheKey;
        ob_start();
        return false;
    }
    
    public function cacheEnd($id = null)
    {
        if (!$this->controller->performCaching) return;
        
        if ($id !== null) $cacheKey = $id;
        else
        {
            $cacheKey = $this->tmpCacheKey;
            $this->tmpCacheKey = null;
        }
        
        $str = ob_get_contents();
        ob_end_clean();
        
        if (!SFileUtils::mkdirs(dirname($cacheKey), 0700, true))
            throw new SException('Caching failed with dirs creation');
            
        file_put_contents($cacheKey, 'Fragment cached !'.$str.'Fragment cached !');
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
    
    private function isFragmentCacheValid($file, $lifetime)
    {
        if (file_exists($file))
        {
            if ($lifetime === null || (time() < filemtime($file) + $lifetime)) return true;
            else return false;
        }
        return false;
    }
    
    private function fragmentCacheKey($id)
    {
        if (is_array($id))
            list($protocol, $id) = explode('://', SUrlRewriter::urlFor($id));
        
        return ROOT_DIR."/cache/fragments/{$id}";
    }
}

?>
