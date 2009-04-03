<?php

class SLogger
{
    // Logging severity threshold
    public $level = null;
    // Logging formatter
    public $formatter = null;
    
    private $path = null;
    private $fp   = false;
    private $shift_age  = 7;
    private $shift_size = 1024000;
    private $default_formatter = null;
    private $severity_labels = array('DEBUG', 'INFO', 'WARNING', 'ERROR', 'FATAL');
    
    const DEBUG = 0;
    const INFO  = 1;
    const WARN  = 2;
    const ERROR = 3;
    const FATAL = 4;
    
    static private $instance = Null;
	
	public static function get_instance()
    {
       return self::$instance;
    }
    
    public static function initialize($path)
    {
        self::$instance = new SLogger($path);
    }
    
    public function __construct($path, $shift_age = 7, $shift_size = 1024000)
    {
        $this->path = $path;
        $this->level = self::DEBUG;
        $this->shift_age = $shift_age;
        $this->shift_size = $shift_size;
        $this->default_formatter = new SApacheFormatter();
    }
    
    public function __destruct()
    {
        $this->close_logfile();
    }
    
    public function log($message, $severity = null)
    {
        if ($severity === null) $severity = self::INFO;
        if ($severity < $this->level) return;
        if ($this->fp === false) $this->open_logfile();
        $line = $this->format_message($message, SDateTime::now(), $this->format_severity($severity));
        return (fwrite($this->fp, $line) !== false);
    }
    
    public function log_error($exception)
    {
        return $this->fatal(get_class($exception)." (".$exception->getMessage().")\n    "
        .implode("\n    ", $this->clean_backtrace($exception))."\n");
    }
    
    public function fatal($message)
    {
        return $this->log($message, self::FATAL);
    }
    
    public function error($message)
    {
        return $this->log($message, self::ERROR);
    }
    
    public function warning($message)
    {
        return $this->log($message, self::WARN);
    }
    
    public function info($message)
    {
        return $this->log($message, self::INFO);
    }
    
    public function debug($message)
    {
        return $this->log($message, self::DEBUG);
    }
    
    private function format_message($msg, $time, $severity)
    {
        if ($this->formatter !== null) return $this->formatter->call($msg, $time, $severity);
        else return $this->default_formatter->call($msg, $time, $severity);
    }
    
    private function format_severity($severity)
    {
        if (!isset($this->severity_labels[$severity])) return 'ANY';
        return $this->severity_labels[$severity];
    }
    
    private function open_logfile()
    {
        if (file_exists($this->path)) $this->check_shift_log();
        else $this->create_logfile();
            
        $this->fp = fopen($this->path, 'a');
        return ($this->fp !== false);
    }
    
    private function close_logfile()
    {
        if ($this->fp !== false && fclose($this->fp))
            $this->fp = false;
        return ($this->fp === false);
    }
    
    private function create_logfile()
    {
        file_put_contents($this->path, '');
    }
    
    private function check_shift_log()
    {
        if ($this->shift_age > 0 && filesize($this->path) > $this->shift_size) $this->shift_log_age();
    }
    
    private function shift_log_age()
    {
        for ($i = ($this->shift_age - 3); $i >= 0; $i--)
            if (file_exists("{$this->path}.{$i}"))
                $this->safe_rename("{$this->path}.{$i}", "{$this->path}.".($i+1));
        
        rename("{$this->path}", "{$this->path}.0");
        $this->create_logfile();
    }
    
    // rename() does not follow the *nix rename convention on Windows...
    private function safe_rename($oldname, $newname)
    {
        if (file_exists($newname)) unlink($newname);
        rename($oldname, $newname);
    }
    
    private function flush()
    {
        return fflush($this->fp);
    }
    
    private function clean_backtrace($exception)
    {
        foreach ($exception->getTrace() as $t)
        {
            $str = '';
            if (isset($t['file']) && isset($t['line'])) $str.= $t['file'].':'.$t['line'];
            else $str.= 'undefined';
            if (isset($t['class'])) $str.= ' in \''.$t['class'].$t['type'].$t['function'].'\'';
            else $str.= ' in \''.$t['function'].'\'';
            $trace [] = $str;
        }
        return $trace;
    }
}

class SApacheFormatter
{
    private $format = "[%s] [%s] Stato: %s\n";
    
    public function call($msg, $time, $severity)
    {
        return sprintf($this->format, $this->format_datetime($time), $severity, $this->msg2str($msg));
    }
    
    private function format_datetime($time)
    {
        return $time->format("%a %b %d %H:%M:%S %Y");
    }
    
    private function msg2str($msg)
    {
        return $msg;
    }
}

class SBasicFormatter
{
    public function call($msg, $time, $severity)
    {
        return "$msg\n";
    }
}

?>
