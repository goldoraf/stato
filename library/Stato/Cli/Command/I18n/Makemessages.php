<?php






class Stato_Cli_Command_I18n_Makemessages extends Stato_Cli_Command
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
        
        $this->addOption('-p', '--path', Stato_Cli_Option::STRING);
        $this->addOption('-l', '--lang', Stato_Cli_Option::STRING);
        $this->addOption('-b', '--backend', Stato_Cli_Option::STRING);
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
        
        if (!isset($options['lang'])) {
            echo "Please provide a language code.\n";
            return;
        }
        
        $backendName = (!isset($options['backend'])) ? 'simple' : $options['backend'];
        $backendClass = 'Stato_I18n_Backend_'.ucfirst($backendName);
        $backend = new $backendClass($localePath);
        
        $this->messages = array();
        
        $it = new RecursiveDirectoryIterator($appPath);
        foreach (new RecursiveIteratorIterator($it) as $file) {
            if ($file->isFile()) $this->extractMessages((string) $file);
        }
        
        foreach ($this->messages as $comment => $message) {
            $backend->addKey($options['lang'], $message, $comment);
        }
        
        $backend->save($options['lang'], $localePath);
    }
    
    private function extractMessages($filepath)
    {
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
                $this->storeMessage(trim($t[1], "'"), $currentFile, $t[2]);
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