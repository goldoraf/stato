<?php

/* TODOS :
- validation mimetypes et size
- verif existence et writability du rep de destination */

class FileMixin
{
    /*public function __construct($values = Null)
    {
        parent::__construct($values);
        $this->metaAttributes = array_merge(array($this->fileNameField), $this->metaAttributes);
    }*/
    
    public static function registerCallbacks($object)
    {
        $object->addSelfCallback('afterDelete', 'removeFile');
    }
    
    public function writeFile($upload)
    {
        if ($upload->isSuccess())
        {
            $filename = $this->sanitizeFileName($upload->name);
            if ($upload->save(ROOT_DIR.'/'.$this->uploadPath(), $filename)) $this->writeFilename($filename); 
        }
    }
    
    public function writeFilename($value)
    {
        return $this->writeAttribute($this->fileNameField(), $value);
    }
    
    public function readFilename()
    {
        return $this->readAttribute($this->fileNameField());
    }
    
    protected function sanitizeFileName($filename)
    {
        $filename = utf8_decode($filename);
    	$filename = strtr($filename,"ÀÁÂÃÄÅàáâãäåÇçÒÓÔÕÖØòóôõöøÈÉÊËèéêëÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ",
                                    "AAAAAAaaaaaaCcOOOOOOooooooEEEEeeeeIIIIiiiiUUUUuuuuyNn");
    	$filename = preg_replace('/[^a-z0-9_.\s]/','',strtolower($filename));
    	$filename = preg_replace('/[\s]+/',' ',trim($filename));
    	return str_replace(' ','-',$filename);
    }
    
    protected function uploadPath()
    {
        return 'data';
    }
    
    protected function fileNameField()
    {
        return 'filename';
    }
    
    protected function removeFile()
    {
        // TODO : suppression du fichier
    }
}

?>
