<?php



class Stato_Webflow_Plugin_PluginAlreadyExist extends Exception {}





class Stato_Webflow_Plugin_Broker extends Stato_Webflow_Plugin implements ArrayAccess, Iterator 
{
    protected $plugins = array();
    
    public function offsetExists($offset)
    {
        return isset($this->plugins[$offset]); 
    }

    public function offsetGet($offset) 
    {
        return $this->plugins[$offset];
    }

    public function offsetSet($offset, $value) 
    {
        if(!($value instanceof Stato_Webflow_Plugin)) {
            throw new InvalidArgumentException('The second parmeter of offsetSet must be a Plugin');
        }
        $this->registerPlugin($value, $offset);
        
    }

    public function offsetUnset($offset) 
    {
        unset($this->plugins[$offset]);
    }

    public function rewind() {
        reset($this->plugins);
    }

    public function current() 
    {
        return current($this->plugins);
    }

    public function key() 
    {
        return key($this->plugins);
    }

    public function next() 
    {
        next($this->plugins);
    }

    public function valid() 
    {
        return current($this->plugins); 
    }

    public function registerPlugin(Stato_Webflow_Plugin $plugin, $offset = null)
    {
        if (array_search($plugin, $this->plugins, true) !== false) {
            throw new Stato_Webflow_Plugin_PluginAlreadyExist('Plugin already registered');
        }
        if($offset !== null && isset($this->plugins[$offset])) {
            throw new Stato_Webflow_Plugin_PluginAlreadyExist('Plugin with offset "' . $offset . '" already registered');
        }

        if ($this->request) {
            $plugin->setRequest($this->request);
        }
        if ($this->response) {
            $plugin->setResponse($this->response);
        }

        if ($offset) {
            $this->plugins[$offset] = $plugin;
        } else {
            $this->plugins[] = $plugin;
        }

        ksort($this->plugins);

        return $this;
    }

    public function unregisterPlugin(Stato_Webflow_Plugin $plugin)
    {
        $key = array_search($plugin, $this->plugins, true);
        if ($key !== false) {
            unset($this->plugins[$key]);
        }
        return $this;
    }

    public function hasPlugin($class)
    {
        foreach ($this->plugins as $plugin) {
            $type = get_class($plugin);
            if ($class == $type) {
                return true;
            }
        }
        return false;
    }

    public function getPlugin($class)
    {
        $found = array();
        foreach ($this->plugins as $plugin) {
            $type = get_class($plugin);
            if ($class == $type) {
                $found[] = $plugin;
            }
        }

        switch (count($found)) {
            case 0:
                return false;
            case 1:
                return $found[0];
            default:
                return $found;
        }
    }

    public function setRequest(Stato_Webflow_Request $request)
    {
        $this->_request = $request;

        foreach ($this->plugins as $plugin) {
            $plugin->setRequest($request);
        }

        return $this;
    }

    public function setResponse(Stato_Webflow_Response $response)
    {
        $this->_response = $response;

        foreach ($this->plugins as $plugin) {
            $plugin->setResponse($response);
        }

        return $this;
    }

    public function preRouting()
    {
        foreach ($this->plugins as $plugin) {
            $plugin->preRouting();
        }
    }

    public function postRouting()
    {
        foreach ($this->plugins as $plugin) {
            $plugin->postRouting();
        }
    }

    public function preDispatch()
    {
        foreach ($this->plugins as $plugin) {
            $plugin->preDispatch();
        }
    }

    public function postDispatch()
    {
        foreach ($this->plugins as $plugin) {
            $plugin->postDispatch();
        }
    }

}	
?>
