#!/usr/bin/php

<?php

set_include_path(dirname(__FILE__).'/cli/lib' . PATH_SEPARATOR . get_include_path());

require 'exception.php';
require 'command.php';
require 'option_parser.php';
require 'command_runner.php';

Stato_Cli_CommandRunner::main($_SERVER['argv']);

exit(0);
