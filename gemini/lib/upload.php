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
    
    public function save($folder, $name = null, $chmod = null)
    {
        if ($name === null) $name = $this->name;
        if ($this->is_success()) $mv_success = @move_uploaded_file($this->temp, $folder.'/'.$name);
        $chmod_success = true;
        if ($chmod !== null && $mv_success) $chmod_success = @chmod($folder.'/'.$name, $chmod);
        return ($mv_success && $chmod_success);
    }
    
    public function is_success()
    {
        return ($this->error == UPLOAD_ERR_OK);
    }
}

?>
