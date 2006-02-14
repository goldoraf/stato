<?php

class SCsvFile
{
    public $fields = array();
    
    private $resource  = Null;
    private $line      = 0;
    private $encodings = array("ASCII", "Windows-1252 (CP1252)", "Windows-1251 (CP1251)");
    
    public function __construct($filePath, $replaceFields = array(), $hasFieldsInFirstLine = True)
    {
        $this->resource = fopen($filePath, "r");
        if ($hasFieldsInFirstLine)  $this->fields = $this->fetch();
        if (!empty($replaceFields)) $this->fields = $replaceFields;
    }
    
    public function __destruct()
    {
        fclose($this->resource);
    }
    
    public function fetch()
    {
        $this->line++;
        return fgetcsv($this->resource, 4096, ";");
    }
    
    public function fetchArray($convert = True)
    {
        $data = $this->fetch();
        if ($data)
        {
            foreach($this->fields as $key => $value)
            {
                if ($convert)
                    $row[$value] = mb_convert_encoding($data[$key], "UTF-8", $this->encodings);
                else
                    $row[$value] = $data[$key];
            }
            return $row;
        }
        return false;
    }
    
    public function fetchObject($objectName)
    {
        if ($data = $this->fetchArray())
        {
            return new $objectName($data);
        }
        return false;
    }
}

?>
