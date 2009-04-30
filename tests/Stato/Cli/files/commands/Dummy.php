<?php

namespace Stato\Cli\Command;

use Stato\Cli\Command;
use Stato\Cli\Option;

class Dummy extends Command
{
    public function __construct()
    {
        parent::__construct();
        $this->shortDesc = 'stato dummy - Dummy command';
        $this->longDesc = 'This is a dummy command.';
        
        $this->addOption('-v', '--verbose', Option::BOOLEAN, null, 'make lots of noise');
    }
    
    public function run($options = array(), $args = array())
    {
        
    }
    
    public function runAnnounce()
    {
        $this->announce('Hello world');
    }
}