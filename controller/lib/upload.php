<?php

class SUpload
{
    public $name = '';
    public $size = '';
    public $temp;
    public $error;
    
    public function __construct($file)
    {
        $this->temp  = $file['tmp_name'];
        $this->error = $file['error'];
        
        if (is_uploaded_file($this->temp))
        {
            $this->name = $file['name'];
            $this->size = $file['size'];
        }
    }
    
    public function save($folder, $name=Null)
    {
        if ($name === Null) $name = $this->name;
        if ($this->is_success() && @move_uploaded_file($this->temp, $folder.'/'.$name))
        {
            return true;
        } 
        return false;
    }
    
    public function is_success()
    {
        if ($this->error == UPLOAD_ERR_OK) return true;
        return false;
    }
}

?>
