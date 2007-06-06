<?php

class SMissingTemplateException extends Exception {}

class SActionView
{
    private $generator = null;
    private $controller  = null;
    private $template_dir = null;
    private $tmp_cache_key = null;
    
    public function __construct($controller = null)
    {
        $this->controller = $controller;
    }
    
    public function __get($name)
    {
        if ($this->controller !== null && isset($this->controller->assigns[$name])) 
            return $this->controller->assigns[$name];
    }
    
    public function __set($name, $value)
    {
        throw new Exception('You\'re not allowed to reassign template variables !');
    }
    
    public function page()
    {
        return $this->generator;
    }
    
    public function render($template, $local_assigns = array())
    {
        if (!is_readable($template))
            throw new SMissingTemplateException($template);
        
        $this->template_dir = dirname($template);
        
        $compiled = $this->compiled_template_path($template);
        
        if (!$this->is_compiled_template($template, $compiled))
            $this->compile($template, $compiled);
            
        extract($local_assigns);
        
        ob_start();
        include ($compiled);
        $str = ob_get_contents();
        ob_end_clean();
        
        return $str;
    }
    
    public function render_update($template, $local_assigns = array())
    {
        if (!is_readable($template))
            throw new SMissingTemplateException($template);
            
        $this->template_dir = dirname($template);
        
        extract($local_assigns);
        
        $this->generator = new SJavascriptGenerator();
        include ($template);
        return $this->generator->__toString();
    }
    
    public function render_partial($partial_path, $local_assigns = array())
    {
        list($path, $partial) = $this->partial_pieces($partial_path);
        $template = "$path/_$partial.php";
        
        return $this->render($template, $local_assigns);
    }
    
    public function render_partial_collection($partial_path, $collection, $spacer_template = null)
    {
        list($path, $partial) = $this->partial_pieces($partial_path);
        $template = "$path/_$partial.php";
        
        $partials_collec = array();
        $counter_name = $partial.'_counter';
        $counter = 1;
        foreach($collection as $element)
        {
            $local_assigns[$counter_name] = $counter;
            $local_assigns[$partial] = $element;
            $partials_collec[] = $this->render($template, $local_assigns);
            $counter++;
        }
        
        if ($spacer_template !== null)
        {
            list($spacer_path, $spacer_partial) = $this->partial_pieces($spacer_template);
            $spacer = "$spacer_path/_$spacer_partial.php";
            return implode($this->render($spacer), $partials_collec);
        }
        else return implode('', $partials_collec);
    }
    
    public function cache_start($id = null, $lifetime = 30)
    {
        if (!SActionController::$perform_caching) return;
        
        if ($id === null) $id = array('controller' => $this->controller->controller_path(),
                                      'action' => $this->controller->action_name());
        
        $cache_key = $this->fragment_cache_key($id);
        if (($cache = $this->read_fragment($cache_key, $lifetime)) !== false)
        {
            echo $cache;
            return true;
        }
        $this->tmp_cache_key = $cache_key;
        ob_start();
        return false;
    }
    
    public function cache_end($id = null)
    {
        if (!SActionController::$perform_caching) return;
        
        if ($id !== null) $cache_key = $id;
        else
        {
            $cache_key = $this->tmp_cache_key;
            $this->tmp_cache_key = null;
        }
        
        $content = ob_get_contents();
        ob_end_clean();
        
        $this->write_fragment($cache_key, $content);
        echo $content;
    }
    
    public function read_fragment($key, $lifetime = 30)
    {
        if ($this->is_fragment_cache_valid($key, $lifetime)) return file_get_contents($key);
        return false;
    }
    
    public function write_fragment($key, $content)
    {
        if (!SDir::mkdirs(dirname($key), 0700, true))
            throw new Exception('Caching failed with dirs creation');
            
        file_put_contents($key, $content);
    }
    
    public function fragment_cache_key($id)
    {
        if (is_array($id))
            $id = SUrlRewriter::url_for(array_merge($id, array('only_path' => true, 
                                                               'skip_relative_url_root' => true)));
        return STATO_APP_ROOT_PATH."/cache/fragments/{$id}";
    }
    
    private function compile($template, $compiled_path)
    {
        $content  = file_get_contents($template);
        $compiled = preg_replace(array('/(<\?=\s)/i', '/(<\?\s)/i', '/(<\%=\s)/i', '/(<\%\s)/i'),
                                 array('<?php echo ', '<?php '), $content);
        
        file_put_contents($compiled_path, $compiled);
        return $compiled_path;
    }
    
    private function is_compiled_template($template, $compiled_path)
    {
        if (!file_exists($compiled_path)) return false;
        if (filemtime($compiled_path) < filemtime($template))
        {
            unlink($compiled_path);
            return false;
        }
        return true;
    }
    
    private function compiled_template_path($template)
    {
        return STATO_APP_ROOT_PATH.'/cache/templates/'.md5($template);
    }
    
    private function partial_pieces($partial_path)
    {
        if (strpos($partial_path, '/') === false)
            return array($this->template_dir, $partial_path);
        else
        {
            $partial = substr(strrchr($partial_path, '/'), 1);
            $sub_path = substr($partial_path, 0, - (strlen($partial) + 1));
            return array(STATO_APP_PATH."/views/$sub_path", $partial);
        }
    }
    
    private function is_fragment_cache_valid($file, $lifetime)
    {
        if (file_exists($file))
        {
            if ($lifetime === null || (time() < filemtime($file) + $lifetime)) return true;
            else return false;
        }
        return false;
    }
}

class SActionCacheFilter
{
    private $actions = array();
    private $rendered_action_cache = false;
    
    public function __construct($actions)
    {
        $this->actions = $actions;
    }
    
    public function before($controller)
    {
        if (!in_array($controller->action_name(), $this->actions)) return;
        if (($cache = $controller->view->read_fragment($this->cache_key($controller))) !== false)
        {
            $this->rendered_action_cache = true;
            $controller->render_text($cache);
            return false;
        }
    }
    
    public function after($controller)
    {
        if (!in_array($controller->action_name(), $this->actions) || $this->rendered_action_cache) return;
        $controller->view->write_fragment($this->cache_key($controller), $controller->response->body);
    }
    
    private function cache_key($controller)
    {
        return $controller->view->fragment_cache_key(
            array('controller' => $controller->controller_path(),
                  'action' => $controller->action_name())
        );
    }
}

?>
