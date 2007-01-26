<?php

class SLogger
{
    private $path = null;
    private $fp   = false;
    private $eol  = null;
    
    static private $instance = Null;
	
	public static function get_instance()
    {
       return self::$instance;
    }
    
    public static function initialize($path)
    {
        if (!file_exists($path)) throw new Exception('Log file does not exist');
        self::$instance = new SLogger($path);
    }
    
    public function __construct($path)
    {
        $this->path = $path;
        $this->eol = (strstr(PHP_OS, 'WIN')) ? "\r\n" : "\n";
    }
    
    public function __destruct()
    {
        $this->close();
    }
    
    public function log($message, $type = null)
    {
        if ($this->fp === false) $this->open();
        if ($type === null) $type = 'info';
        $line = $message.$this->eol;
        return (fwrite($this->fp, $line) !== false);
    }
    
    public function log_with_time($message, $type = null)
    {
        $line = '['.strftime('%Y-%m-%d %H:%M:%S').']'." [$type] $message".$this->eol;
        return $this->log($line, $type);
    }
    
    public function fatal($message)
    {
        return $this->log($message, 'fatal');
    }
    
    public function warning($message)
    {
        return $this->log($message, 'warning');
    }
    
    public function notice($message)
    {
        return $this->log($message, 'notice');
    }
    
    public function info($message)
    {
        return $this->log($message, 'info');
    }
    
    public function debug($message)
    {
        return $this->log($message, 'debug');
    }
    
    private function open()
    {
        $this->fp = fopen($this->path, 'a');
        return ($this->fp !== false);
    }
    
    private function close()
    {
        if ($this->fp !== false && fclose($this->fp))
            $this->fp = false;
        return ($this->fp === false);
    }
    
    private function flush()
    {
        return fflush($this->fp);
    }
}

?>
