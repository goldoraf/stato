<?php

abstract class SCacheStore
{
    abstract public function read($key, $lifetime = 0);
    
    abstract public function write($key, $content, $lifetime = 0);
    
    abstract public function delete($key);
}

class SFileStore extends SCacheStore
{
    public function read($key, $lifetime = 0)
    {
        if ($this->is_valid($this->real_file_path($key), $lifetime)) 
            return file_get_contents($this->real_file_path($key));
        return false;
    }
    
    public function write($key, $content, $lifetime = 0)
    {
        if (!SDir::mkdirs(dirname($this->real_file_path($key)), 0700, true))
            throw new Exception('Caching failed with dirs creation');
            
        file_put_contents($this->real_file_path($key), $content);
    }
    
    public function delete($key)
    {
        @unlink($this->real_file_path($key));
    }
    
    private function is_valid($file, $lifetime = 0)
    {
        if (file_exists($file))
            return ($lifetime == 0 || (time() < filemtime($file) + $lifetime));
        
        return false;
    }
    
    private function real_file_path($key)
    {
        return STATO_APP_ROOT_PATH.SActionController::$file_store_path.'/'.$key;
    }
}

class SMemcacheStore extends SCacheStore
{
    private $mem;
    
    public function __construct()
    {
        $servers = SActionController::$memcache_hosts;
        $this->mem = new Memcache();
        $ok = $this->mem->connect(array_shift($servers), 11211);
        if (!$ok) throw new Exception("Unable to connect to memcache server");
        foreach ($servers as $s) $this->mem->addServer($s, 11211);
    }
    
    public function read($key, $lifetime = 0)
    {
        return $this->mem->get($key);
    }
    
    public function write($key, $content, $lifetime = 0)
    {
        $this->mem->set($key, $content, false, $lifetime);
    }
    
    public function delete($key)
    {
        $this->mem->delete($key);
    }
}

?>
