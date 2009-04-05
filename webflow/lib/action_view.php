<?php

class SMissingTemplateException extends Exception {}

class SActionView
{
    private $controller  = null;
    private $template_dir = null;
    private $tmp_cache_key = null;
    private $fragment_cache_store = null;
    
    public function __construct($controller = null)
    {
        $this->controller = $controller;
        $cache_store_class = 'S'.SActionController::$fragment_cache_store.'Store';
        $this->fragment_cache_store = new $cache_store_class();
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
    
    public function __isset($name)
    {
        return $this->controller !== null && isset($this->controller->assigns[$name]);
    }
    
    public function __unset($name)
    {
        throw new Exception('You\'re not allowed to unset template variables !');
    }
    
    public function render($template, $local_assigns = array(), $is_partial = false)
    {
        if (!is_readable($template))
            throw new SMissingTemplateException($template);
        
        if (!$is_partial)
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
    
    public function render_partial($partial_path, $local_assigns = array())
    {
        list($path, $partial) = $this->partial_pieces($partial_path);
        $template = "$path/_$partial.php";
        
        return $this->render($template, $local_assigns, true);
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
            $partials_collec[] = $this->render($template, $local_assigns, true);
            $counter++;
        }
        
        if ($spacer_template !== null)
        {
            list($spacer_path, $spacer_partial) = $this->partial_pieces($spacer_template);
            $spacer = "$spacer_path/_$spacer_partial.php";
            return implode($this->render($spacer, array(), true), $partials_collec);
        }
        else return implode('', $partials_collec);
    }
    
    public function cache_start($key = null, $lifetime = 0)
    {
        if (!SActionController::$perform_caching) return;
        
        if ($key === null) $key = array('controller' => $this->controller->controller_name(),
                                        'action' => $this->controller->action_name());
        
        if (($cache = $this->read_fragment($key, $lifetime)) !== false)
        {
            echo $cache;
            return true;
        }
        $this->tmp_cache_key = $key;
        ob_start();
        return false;
    }
    
    public function cache_end($key = null, $lifetime = 0)
    {
        if (!SActionController::$perform_caching) return;
        
        if ($key === null)
        {
            $key = $this->tmp_cache_key;
            $this->tmp_cache_key = null;
        }
        
        $content = ob_get_contents();
        ob_end_clean();
        
        $this->write_fragment($key, $content, $lifetime);
        echo $content;
    }
    
    public function read_fragment($key, $lifetime = 0)
    {
        if (!SActionController::$perform_caching) return;
        
        return $this->fragment_cache_store->read($this->fragment_cache_key($key), $lifetime);
        
    }
    
    public function write_fragment($key, $content, $lifetime = 0)
    {
        if (!SActionController::$perform_caching) return;
        
        $this->fragment_cache_store->write($this->fragment_cache_key($key), $content, $lifetime);
    }
    
    public function expire_fragment($key)
    {
        if (!SActionController::$perform_caching) return;
        
        $this->fragment_cache_store->delete($this->fragment_cache_key($key));
        
    }
    
    public function fragment_cache_key($key)
    {
        if (is_array($key))
            $key = SUrlRewriter::url_for(array_merge($key, array('only_path' => true, 
                                                                 'skip_relative_url_root' => true)));
        return $key;
    }
    
    private function compile($template, $compiled_path)
    {
        $content  = file_get_contents($template);
        $compiled = preg_replace(array('/(<\?=\s)/i', '/(<\?\s)/i', '/(<\%=\s)/i', '/(<\%\s)/i', '/(<\?=h\s)(.*);\s\?>/i'),
                                 array('<?php echo ', '<?php ', '<?php echo ', '<?php ', '<?php echo html_escape(${2}); ?>'), $content);
        
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
            return array(dirname($this->template_dir)."/$sub_path", $partial);
        }
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
            array('controller' => $controller->controller_name(),
                  'action' => $controller->action_name())
        );
    }
}

?>