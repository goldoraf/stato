<?php

require STATO_CORE_PATH.'/mercury/lib/db_schema.php';

class DbSchemaImportCommand extends SCommand
{
    public function execute()
    {
        $schema_file_path = STATO_APP_ROOT_PATH.'/db/db_schema.php';
        
        if (!file_exists($schema_file_path))
            throw new SConsoleException("Schema file not found");
            
        $tables = include $schema_file_path;
        if (!is_array($tables))
            throw new SConsoleException("Schema file must return an array of STable objects");
        
        echo "Importing DB schema...\n";
        
        foreach ($tables as $t)
        {
            if (get_class($t) != 'STable')
                throw new SConsoleException("Schema file must return an array of STable objects");
                
            echo "    create table ".$t->name()."\n";
            
            $t->create();
        }
    }
}

?>
