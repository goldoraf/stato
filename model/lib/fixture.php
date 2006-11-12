<?php

class SFixture
{
    const CSV_MODE = 1;
    const INI_MODE = 2;
    
    private $db = Null;
    private $mode = Null;
    private $class_name = Null;
    private $table_name = Null;
    private $values = array();
    
    public static function create_fixtures($fixtures_dir, $table_names)
    {
        $db = SActiveRecord::connection();
        $fixtures = array();
        foreach ($table_names as $table)
        {
            $fixture_path = $fixtures_dir.'/'.$table;
            if (file_exists($fixture_path.'.csv')) $mode = SFixture::CSV_MODE;
            else $mode = SFixture::INI_MODE;
            $fixtures[$table] = new SFixture($db, $table, $fixture_path, $mode);
        }
        return $fixtures;
    }
    
    public function __construct($db, $table_name, $fixture_path, $mode = self::CSV_MODE)
    {
        $this->db = $db;
        $this->mode = $mode;
        $this->class_name = ucfirst(SInflection::singularize($table_name));
        if (class_exists($this->class_name)) SActiveRecordMeta::add_manager_to_class($this->class_name);
        $this->table_name = $table_name;
        $this->fixture_path = $fixture_path;
        $this->read_fixture_file();
    }
    
    /*public function instanciate_fixtures()
    {
        $instances = array();
        $class = $this->class_name;
        if (class_exists($class))
        {
            $temp = new $class(null, true);
            $pk = $temp->identity_field;
            foreach($this->values as $obj => $values)
            {
                $instances[$obj] = SActiveStore::find_by_pk($class, $values[$pk]);
            }
            return $instances;
        }
        return false;
    }*/
    
    public function delete_existing_fixtures()
    {
        $this->db->execute("DELETE FROM {$this->table_name}");
    }
      
    public function insert_fixtures()
    {
        foreach($this->values as $obj => $values)
        {
            $this->db->execute("INSERT INTO {$this->table_name} (".implode(', ', array_keys($values)).")"
                ." VALUES (".implode(', ', $this->db->array_quote(array_values($values))).")");
        }
    }
    
    private function read_fixture_file()
    {
        if ($this->mode == self::CSV_MODE)
        {
            $csv = new SCsvIterator(fopen($this->fixture_path.'.csv', 'r'));
            $i = 1;
            foreach($csv as $data)
            {
                $this->values[strtolower($this->class_name).'_'.$i] = $data;
                $i++;
            }
        }
        elseif ($this->mode == self::INI_MODE)
        {
            $this->values = parse_ini_file($this->fixture_path.'.ini', true);
        }
    }
}

?>
