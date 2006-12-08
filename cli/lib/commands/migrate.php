<?php

class MigrateCommand extends SCommand
{
    protected $allowed_options = array('version' => true);
    
    public function execute()
    {
        if (isset($this->options['version'])) $version = $this->options['version'];
        else $version = null;
        
        SMigrator::migrate(STATO_APP_ROOT_PATH.'/db/migrate', $version);
    }
}

?>
