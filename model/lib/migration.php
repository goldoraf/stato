<?php

class SIrreversibleMigrationException extends SException {}
class SDuplicateVersionMigrationException extends SException
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
    
    public function __call($method, $args)
    {
        call_user_func_array(array(SActiveRecord::connection(), $method), $args);
    }
}

class SMigrator
{
    private $migrationsPath = null;
    private $targetVersion  = null;
    private $direction      = null;
    
    public static $schemaInfoTableName = 'schema_info';
    
    public static function migrate($migrationsPath, $targetVersion = null)
    {
        SActiveRecord::connection()->initializeSchemaInformation();
        $currentVersion = self::currentVersion();
        if ($targetVersion === null || $currentVersion < $targetVersion)
            self::up($migrationsPath, $targetVersion);
        elseif ($currentVersion > $targetVersion)
            self::down($migrationsPath, $targetVersion);
        elseif ($currentVersion == $targetVersion)
            return;
    }
    
    public static function up($migrationsPath, $targetVersion = null)
    {
        $m = new SMigrator('up', $migrationsPath, $targetVersion);
        $m->executeMigration();
    }
    
    public static function down($migrationsPath, $targetVersion = null)
    {
        $m = new SMigrator('down', $migrationsPath, $targetVersion);
        $m->executeMigration();
    }
    
    public static function currentVersion()
    {
        $row = SActiveRecord::connection()->selectOne('SELECT version FROM '.self::$schemaInfoTableName);
        return $row['version'];
    }
    
    public function __construct($direction, $migrationsPath, $targetVersion)
    {
        $this->direction = $direction;
        $this->migrationsPath = $migrationsPath;
        $this->targetVersion = $targetVersion;
        SActiveRecord::connection()->initializeSchemaInformation();
    }
    
    public function executeMigration()
    {
        foreach ($this->migrationFiles() as $file)
        {
            require_once($this->migrationsPath.'/'.$file);
            list($version, $name) = $this->versionAndName($file);
            if ($this->reachedTargetVersion($version)) break;
            if (!$this->isIrrelevantMigration($version))
            {
                $class = $this->migrationClass($name);
                echo "Migrating to $class ($version)\n";
                $migration = new $class();
                $migration->migrate($this->direction);
                $this->setSchemaVersion($version);
            }
        }
    }
    
    private function migrationFiles()
    {
        $files = array();
        $dir = new DirectoryIterator($this->migrationsPath);
        foreach ($dir as $file)
        {
            if (preg_match('/([0-9]+)_([_a-z0-9]*)/i', $file->getFileName(), $matches))
            {
                if (isset($files[$matches[1]])) throw new SDuplicateVersionMigrationException($matches[1]);
                else $files[$matches[1]] = $file->getFileName();
            }
        }
        natsort($files);
        return $this->isDown() ? array_reverse($files) : $files;
    }
    
    private function migrationClass($name)
    {
        return SInflection::camelize($name);
    }
    
    private function versionAndName($file)
    {
        preg_match('/([0-9]+)_([_a-z0-9]*)/i', $file, $matches);
        return array($matches[1], $matches[2]);
    }
    
    private function setSchemaVersion($version)
    {
        SActiveRecord::connection()->update('UPDATE '.self::$schemaInfoTableName
        .' SET version = '.($this->isDown() ? $version -1 : $version));
    }
    
    private function isUp()
    {
        return $this->direction == 'up';
    }
    
    private function isDown()
    {
        return $this->direction == 'down';
    }
    
    private function reachedTargetVersion($version)
    {
        return (($this->isUp() && $version - 1 === $this->targetVersion) 
                || ($this->isDown() && $version == $this->targetVersion));
    }
    
    private function isIrrelevantMigration($version)
    {
        return (($this->isUp() && $version <= self::currentVersion()) 
                || ($this->isDown() && $version > self::currentVersion()));
    }
}

?>
