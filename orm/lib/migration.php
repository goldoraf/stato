<?php

class SIrreversibleMigrationException extends Exception {}
class SDuplicateVersionMigrationException extends Exception
{
    public function __construct($version)
    {
        parent::__construct("Multiple migrations have the version number {$version}");
    }
}

abstract class SMigration
{
    abstract public function up();
    
    abstract public function down();
    
    public function migrate($direction)
    {
        if ($direction == 'up') $this->announce('migrating');
        else $this->announce('reverting');
        
        $start = microtime(true);
        $this->$direction();
        $end = microtime(true);
        $time = $end - $start;
        
        if ($direction == 'up') $this->announce(sprintf("migrated (%.4fs)", $time));
        else $this->announce(sprintf("reverted (%.4fs)", $time));
    }
    
    public function announce($message)
    {
        $name = get_class($this);
        echo "=> $name: $message\n";
    }
    
    public function ask($message)
    {
        $this->announce("$message (y/n)");
        $answer = trim(fgets(STDIN, 1024));
        return $answer == 'y';
    }
    
    public function __call($method, $args)
    {
        return call_user_func_array(array(SActiveRecord::connection(), $method), $args);
    }
}

class SMigrator
{
    private $migrations_path = null;
    private $target_version  = null;
    private $direction      = null;
    
    public static $schema_info_table_name = 'schema_info';
    
    public static function migrate($migrations_path, $target_version = null)
    {
        SActiveRecord::connection()->initialize_schema_information();
        $current_version = self::current_version();
        if ($target_version === null || $current_version < $target_version)
            self::up($migrations_path, $target_version);
        elseif ($current_version > $target_version)
            self::down($migrations_path, $target_version);
        elseif ($current_version == $target_version)
            return;
    }
    
    public static function up($migrations_path, $target_version = null)
    {
        $m = new SMigrator('up', $migrations_path, $target_version);
        $m->execute_migration();
    }
    
    public static function down($migrations_path, $target_version = null)
    {
        $m = new SMigrator('down', $migrations_path, $target_version);
        $m->execute_migration();
    }
    
    public static function current_version()
    {
        $row = SActiveRecord::connection()->select_one('SELECT version FROM '.self::$schema_info_table_name);
        return $row['version'];
    }
    
    public static function migration_files($migrations_path)
    {
        $files = array();
        $dir = new DirectoryIterator($migrations_path);
        foreach ($dir as $file)
        {
            if (preg_match('/^([0-9]+)_([_a-z0-9]*)/i', $file->getFileName(), $matches))
            {
                if (isset($files[$matches[1]])) throw new SDuplicateVersionMigrationException($matches[1]);
                else $files[$matches[1]] = $file->getFileName();
            }
        }
        natsort($files);
        return $files;
    }
    
    public static function last_version($migrations_path)
    {
        $files = self::migration_files($migrations_path);
        if (empty($files)) return 0;
        return end(array_keys($files));
    }
    
    public function __construct($direction, $migrations_path, $target_version)
    {
        $this->direction = $direction;
        $this->migrations_path = $migrations_path;
        $this->target_version = $target_version;
        SActiveRecord::connection()->initialize_schema_information();
    }
    
    public function execute_migration()
    {
        $migration_files = self::migration_files($this->migrations_path);
        if ($this->is_down()) $migration_files = array_reverse($migration_files);
        
        foreach ($migration_files as $file)
        {
            require_once($this->migrations_path.'/'.$file);
            list($version, $name) = $this->version_and_name($file);
            if ($this->reached_target_version($version)) break;
            if (!$this->is_irrelevant_migration($version))
            {
                $class = $this->migration_class($name);
                echo "Migrating to $class ($version)\n";
                $migration = new $class();
                $migration->migrate($this->direction);
                $this->set_schema_version($version);
            }
        }
    }
    
    private function migration_class($name)
    {
        return SInflection::camelize($name);
    }
    
    private function version_and_name($file)
    {
        preg_match('/([0-9]+)_([_a-z0-9]*)/i', $file, $matches);
        return array($matches[1], $matches[2]);
    }
    
    private function set_schema_version($version)
    {
        SActiveRecord::connection()->update('UPDATE '.self::$schema_info_table_name
        .' SET version = '.($this->is_down() ? $version -1 : $version));
    }
    
    private function is_up()
    {
        return $this->direction == 'up';
    }
    
    private function is_down()
    {
        return $this->direction == 'down';
    }
    
    private function reached_target_version($version)
    {
        return (($this->is_up() && $this->target_version !== null && $version - 1 == $this->target_version) 
                || ($this->is_down() && $version == $this->target_version));
    }
    
    private function is_irrelevant_migration($version)
    {
        return (($this->is_up() && $version <= self::current_version()) 
                || ($this->is_down() && $version > self::current_version()));
    }
}

?>
