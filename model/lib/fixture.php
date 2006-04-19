<?php

class SFixture
{
    const CSV_MODE = 1;
    const INI_MODE = 2;
    
    private $db = Null;
    private $mode = Null;
    private $className = Null;
    private $tableName = Null;
    private $values = array();
    
    public static function createFixtures($fixturesDir, $tableNames)
    {
        $db = SActiveRecord::connection();
        $fixtures = array();
        foreach ($tableNames as $table)
        {
            $fixturePath = $fixturesDir.'/'.$table;
            if (file_exists($fixturePath.'.csv')) $mode = SFixture::CSV_MODE;
            else $mode = SFixture::INI_MODE;
            $fixtures[$table] = new SFixture($db, $table, $fixturePath, $mode);
        }
        return $fixtures;
    }
    
    public function __construct($db, $tableName, $fixturePath, $mode = self::CSV_MODE)
    {
        $this->db = $db;
        $this->mode = $mode;
        $this->className = ucfirst(SInflection::singularize($tableName));
        $this->tableName = $tableName;
        $this->fixturePath = $fixturePath;
        $this->readFixtureFile();
    }
    
    public function instanciateFixtures()
    {
        $instances = array();
        $class = $this->className;
        if (class_exists($class))
        {
            $temp = new $class(null, true);
            $pk = $temp->identityField;
            foreach($this->values as $obj => $values)
            {
                $instances[$obj] = SActiveStore::findByPk($class, $values[$pk]);
            }
            return $instances;
        }
        return false;
    }
    
    public function deleteExistingFixtures()
    {
        $this->db->execute("DELETE FROM {$this->tableName}");
    }
      
    public function insertFixtures()
    {
        foreach($this->values as $obj => $values)
        {
            $this->db->execute("INSERT INTO {$this->tableName} (".implode(', ', array_keys($values)).")"
                ." VALUES (".implode(', ', SActiveStore::arrayQuote(array_values($values))).")");
        }
    }
    
    private function readFixtureFile()
    {
        if ($this->mode == self::CSV_MODE)
        {
            $csv = new SCsvIterator(fopen($this->fixturePath.'.csv', 'r'));
            $i = 1;
            foreach($csv as $data)
            {
                $this->values[strtolower($this->className).'_'.$i] = $data;
                $i++;
            }
        }
        elseif ($this->mode == self::INI_MODE)
        {
            $this->values = parse_ini_file($this->fixturePath.'.ini', true);
        }
    }
}

?>
