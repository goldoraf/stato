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

/*class csvstream{
   var $position; 
   var $varname; 
   function stream_open($path, $mode, $options, &$opened_path){ 
       $url = parse_url($path); 
       $this->varname = $url['host'] ;
       $this->position = 0; 
       return true;
   }
  function stream_read($count){ 
       $ret = substr($GLOBALS[$this->varname], $this->position, $count); 
       $this->position += strlen($ret); 
       return $ret; 
   }
  function stream_eof(){ 
       return $this->position >= strlen($GLOBALS[$this->varname]); 
   } 
   function stream_tell(){ 
       return $this->position; 
   } 
}

   stream_wrapper_register("csvstr", "csvstream") ;
   $str="yak, llama, 'big llama', 'wobmat, with a comma in it', bandycoot";

   $fp = fopen("csvstr://str", "r+"); 
   print_r(fgetcsv($fp,100,",","'"));*/


?>
