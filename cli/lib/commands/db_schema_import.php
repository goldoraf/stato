<?php

require STATO_CORE_PATH.'/mercury/lib/db_schema.php';

class DbSchemaImportCommand extends SCommand
{
    public function execute()
    {
        $schema_file_path = STATO_APP_ROOT_PATH.'/db/db_schema.php';
        
        if (!file_exists($schema_file_path))
            throw new SConsoleException("Schema file not found");
            
        $classes = $this->get_classes_in_file($schema_file_path);
        if (count($classes) != 1)
            throw new SConsoleException("Schema file should not contain more than one class");
        
        echo "Importing DB schema...\n";
        
        $schema_class = array_pop($classes);
        $instance = new $schema_class;
        $instance->define();
    }
    
    private function get_classes_in_file($file_path)
    {
        $before_classes = get_declared_classes();
        require $file_path;
        $after_classes  = get_declared_classes();
        return array_diff($after_classes, $before_classes);
    }
}

?>
