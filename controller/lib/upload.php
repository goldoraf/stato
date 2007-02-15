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
    
    public function save($folder, $name = null)
    {
        if ($name === null) $name = $this->name;
        return ($this->is_success() && @move_uploaded_file($this->temp, $folder.'/'.$name));
    }
    
    public function is_success()
    {
        return ($this->error == UPLOAD_ERR_OK);
    }
}

?>
