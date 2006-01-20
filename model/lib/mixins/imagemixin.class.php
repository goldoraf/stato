<?php

class ImageMixin
{
    public static function registerCallbacks($object)
    {
        $object->addSelfCallback('afterDelete', 'removeFiles');
    }
    
    public function filepath()
    {
        return $this->uploadPath().'/'.$this->readFilename();
    }
    
    public function thumbpath()
    {
        return $this->uploadPath().'/thumbs/'.$this->readFilename();
    }
    
    public function writeFile($upload)
    {
        if ($upload->isSuccess())
        {
            $filename = $this->sanitizeFileName($upload->name);
            if ($upload->save(ROOT_DIR.'/'.$this->uploadPath(), $filename))
            {
                $this->writeFilename($filename);
                $img = new Image(ROOT_DIR.'/'.$this->uploadPath().'/'.$filename);
                $img->resize(32, ROOT_DIR.'/'.$this->uploadPath().'/thumbs/'.$filename);
            }
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
        return 'data/photos';
    }
    
    protected function fileNameField()
    {
        return 'filename';
    }
    
    protected function removeFiles()
    {
        // TODO : suppression des fichiers
    }
}

?>
