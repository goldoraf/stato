<?php

namespace Stato\Cli\Command;

use Stato\Cli\Command;
use Stato\Cli\Option;

class Foo extends Command
{
    public function __construct()
    {
        parent::__construct();
        $this->shortDesc = 'stato foo - Dummy command';
        $this->longDesc = 'This is a dummy command.';
        
        $this->addOption('-u', '--user', Option::STRING, 'username', 'greet user');
    }
    
    public function run($options = array(), $args = array())
    {
        $this->announce("Hello {$options['username']}");
    }
}