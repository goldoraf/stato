<?php

namespace Stato\Cli\Command\I18n;

use Stato\Cli\Command;
use Stato\Cli\Option;

class Makemessages extends Command
{
    private $rootPath;
    private $tokens;
    private $messages;
    private $functions = array('__', '_f', '_p');
    
    public function __construct()
    {
        parent::__construct();
        $this->shortDesc = 'stato i18n:makemessages - Create a message file for a new language';
        $this->longDesc = 'The command runs over your application source tree and pulls out all strings marked for translation.';
        
        $this->addOption('-p', '--path', Option::STRING);
        $this->addOption('-l', '--lang', Option::STRING);
    }
    
    public function run($options = array(), $args = array())
    {
        if (isset($options['path'])) $this->rootPath = rtrim($options['path'], '/');
        else $this->rootPath = getcwd();
        
        $appPath = $this->rootPath.'/app';
        $localePath = $this->rootPath.'/locale';
        
        if (!file_exists($appPath))  {
            echo "It looks like you're not at the root directory of a Stato project.\n";
            return;
        }
        
        $it = new \RecursiveDirectoryIterator($appPath);
        foreach (new \RecursiveIteratorIterator($it) as $file) {
            if ($file->isFile()) $this->extractMessages((string) $file);
        }
    }
    
    private function extractMessages($filepath)
    {
        $this->messages = array();
        $this->tokens = token_get_all(file_get_contents($filepath));
        
        while ($token = current($this->tokens)) {
            if (!is_string($token)) {
                list($id, $text) = $token;
                if ($id == T_STRING && in_array($text, $this->functions)) {
                    $this->processMessage($filepath);
                    continue;
                }
            }
            next($this->tokens);
        }
    }
    
    private function processMessage($currentFile)
    {
        next($this->tokens);
        while ($t = current($this->tokens)) {
            if (is_string($t) || (is_array($t) && ($t[0] == T_WHITESPACE || $t[0] == T_DOC_COMMENT || $t[0] == T_COMMENT))) {
                next($this->tokens);
            } else {
                $this->storeMessage($t[1], $currentFile, $t[2]);
                next($this->tokens);
                return;
            }
        }
    }
    
    private function storeMessage($message, $currentFile, $line)
    {
        $file = str_replace($this->rootPath.'/', '', $currentFile);
        $this->messages[$file.':'.$line] = $message;
    }
}