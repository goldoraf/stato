<?php



abstract class Stato_Cli_Command
{
    protected $shortDesc;
    
    protected $longDesc;
    
    protected $options;
    
    abstract public function run($options = array(), $args = array());
    
    public function __construct()
    {
        $this->options = array();
    }
    
    public function getOptions()
    {
        return $this->options;
    }
    
    public function getHelp()
    {
        $help = "{$this->shortDesc}\n\n{$this->longDesc}\n\noptions:\n";
        foreach ($this->options as $o) $help.= $o->getHelp();
        return $help;
    }
    
    protected function addOption($short, $long, $type = Stato_Cli_Option::BOOLEAN, $dest = null, $help = null, $metavar = null)
    {
        $this->options[] = new Stato_Cli_Option($short, $long, $type, $dest, $help, $metavar);
    }
    
    protected function announce($message)
    {
        echo "    $message\n";
    }
    
    protected function ask($message)
    {
        // @codeCoverageIgnoreStart
        echo "    $message (y/n) ";
        $answer = trim(fgets(STDIN, 1024));
        return $answer == 'y';
        // @codeCoverageIgnoreStop
    }
    
    protected function mkdir($dirname, $path)
    {
        $dirpath = "$path/$dirname";
        if (file_exists($dirpath))
            $this->announce("exists $dirpath");
        else {
            $this->announce("create $dirpath");
            mkdir($dirpath);
        }
    }
}