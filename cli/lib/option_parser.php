<?php

class Stato_Cli_Option
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
            throw new Stato_Cli_Exception("unknown $type type option");
            
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

class Stato_Cli_OptionParser
{
    const SHORT = 'short';
    
    const LONG = 'long';
    
    private $options = array();
    
    private $shortOptions = array();
    
    private $longOptions = array();
    
    public function __construct($options = array())
    {
        foreach ($options as $option) $this->addOptionObject($option);
    }
    
    public function addOption($short, $long, $type = Stato_Cli_Option::BOOLEAN, $dest = null, $help = null, $metavar = null)
    {
        $option = new Stato_Cli_Option($short, $long, $type, $dest, $help, $metavar);
        $this->addOptionObject($option);
    }
    
    public function parseArgs(array $args)
    {
        if (empty($args)) return array(array(), array());
        
        $opts = array();
        $nonOpts = array();

        while (count($args)) {
            if ($args[0]{0} == '-') {
                $opt = array_shift($args);
                $potentialValue = (!empty($args) && $args[0]{0} != '-') ? array_shift($args) : null;
                if ($opt{1} == '-') {
                    list($k, $v) = $this->parseLongOption($opt, $potentialValue);
                    $opts[$k] = $v;
                } else {
                    if (strlen($opt) > 2) {
                        if ($potentialValue !== null)
                            throw new Stato_Cli_Exception("multiple options $opt can't be foolowed by an argument");
                        $opts = array_merge($opts, $this->parseMultipleOptions($opt));
                    } else {
                        list($k, $v) = $this->parseShortOption($opt, $potentialValue);
                        $opts[$k] = $v;
                    }
                }
            }
            else $nonOpts[] = array_shift($args);
        }
        
        return array($opts, $nonOpts);
    }
    
    private function addOptionObject(Stato_Cli_Option $option)
    {
        $this->options[] = $option;
        $this->shortOptions[] = $option->short;
        $this->longOptions[] = $option->long;
    }
    
    private function parseShortOption($opt, $potentialValue = null)
    {
        $option = $this->searchOption($opt, self::SHORT);
        return $this->processOptionValue($opt, $option, $potentialValue);
    }
    
    private function parseMultipleOptions($opts)
    {
        $options = array();
        $opts = str_split(substr($opts, 1));
        foreach ($opts as $opt) {
            $option = $this->searchOption('-'.$opt, self::SHORT);
            if ($option->type == Stato_Cli_Option::STRING)
                throw new Stato_Cli_Exception("option -$opt requires an argument");
            $options[$option->dest] = true;
        }
        return $options;
    }
    
    private function parseLongOption($opt, $potentialValue = null)
    {
        if (strpos($opt, '=') !== false) {
            if ($potentialValue !== null)
                throw new Stato_Cli_Exception("option $opt has been provided with 2 arguments");
            list($opt, $potentialValue) = explode('=', $opt);
        }
        $option = $this->searchOption($opt, self::LONG);
        return $this->processOptionValue($opt, $option, $potentialValue);
    }
    
    private function searchOption($opt, $type = self::SHORT)
    {
        $prop = ($type == self::SHORT) ? 'shortOptions' : 'longOptions';
        $k = array_search($opt, $this->{$prop});
        if ($k === false) throw new Stato_Cli_Exception("unrecognized option $opt");
        return $this->options[$k];
    }
    
    private function processOptionValue($opt, Stato_Cli_Option $option, $potentialValue = null)
    {
        if ($option->type == Stato_Cli_Option::BOOLEAN) {
            if ($potentialValue !== null)
                throw new Stato_Cli_Exception("option $opt doesn't allow an argument");
            return array($option->dest, true);
        } else {
            if ($potentialValue === null)
                throw new Stato_Cli_Exception("option $opt requires an argument");
            return array($option->dest, $potentialValue);
        }
    }
}