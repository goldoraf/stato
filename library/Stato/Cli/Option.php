<?php

namespace Stato\Cli;

class Option
{
    const BOOLEAN = 'boolean';
    
    const STRING = 'string';
    
    public $short;
    
    public $long;
    
    public $type;
    
    public $dest;
    
    public $help;
    
    public $metavar;
    
    public function __construct($short, $long, $type = self::BOOLEAN, $dest = null, $help = null, $metavar = null)
    {
        if ($type != self::BOOLEAN && $type != self::STRING)
            throw new Exception("unknown $type type option");
            
        $this->short = $short;
        $this->long = $long;
        $this->type = $type;
        $this->dest = ($dest !== null) ? $dest : substr($this->long, 2);
        $this->help = $help;
        if ($this->type == self::STRING) {
            if ($metavar !== null) $this->metavar = $metavar;
            elseif ($dest !== null) $this->metavar = strtoupper($dest);
            else $this->metavar = strtoupper(substr($long, 2));
        }
    }
    
    public function getHelp()
    {
        if ($this->metavar !== null)
            $option = "  {$this->short} {$this->metavar}, {$this->long}={$this->metavar}";
        else
            $option = "  {$this->short}, {$this->long}";
        if (strlen($option) < 27)
            return str_pad($option, 27)."{$this->help}\n";
        else
            return "$option\n".str_repeat(' ', 27)."$this->help\n";
    }
}