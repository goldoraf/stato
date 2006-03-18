<?php

class SImage 
{
    public $width  = Null;
    public $height = Null;
    public $type   = Null;
    public $path   = Null;
    
    public function __construct($path)
    {
        $attributes   = getimagesize($path);
        $this->width  = $attributes[0];
        $this->height = $attributes[1];
        $this->type   = $attributes[2];
        $this->path   = $path;
    }
    
    public function resize($maxDim='32', $path)
    {
        if (!function_exists('gd_info') || !$this->isResizeSafe()) return False;
        
        switch ($this->type)
        {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($this->path);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($this->path);
                break;
            default:
                return False;
        }
        
        if ($this->width > $this->height)
        {
            $thX = $maxDim;
            $thY = $maxDim * ((float)$this->height/(float)$this->width);
        }
        else
        {
            $thX = $maxDim * ((float)$this->width/(float)$this->height);
            $thY = $maxDim;
        }
        $thumbnail = imagecreatetruecolor($thX, $thY);
        imagecopyresampled ($thumbnail, $image, 0, 0, 0, 0 ,$thX, $thY, $this->width, $this->height);
        
        if (!imagejpeg($thumbnail, $path)) return False;
        return True;
    }
    
    public function mimetype()
    {
        return image_type_to_mime_type($this->type);
    }
    
    // une fonction image_type_to_extension a été implémentée
    // dans le CVS de PHP. A suivre...
    public function extension($includeDot=true)
    {
        $dot = $includeDot ? '.' : '';
        switch($this->type)
        {
           case IMAGETYPE_GIF    : return $dot.'gif';
           case IMAGETYPE_JPEG   : return $dot.'jpg';
           case IMAGETYPE_PNG    : return $dot.'png';
           case IMAGETYPE_SWF    : return $dot.'swf';
           case IMAGETYPE_PSD    : return $dot.'psd';
           case IMAGETYPE_WBMP   : return $dot.'wbmp';
           case IMAGETYPE_XBM    : return $dot.'xbm';
           case IMAGETYPE_TIFF_II : return $dot.'tiff';
           case IMAGETYPE_TIFF_MM : return $dot.'tiff';
           case IMAGETYPE_IFF    : return $dot.'aiff';
           case IMAGETYPE_JB2    : return $dot.'jb2';
           case IMAGETYPE_JPC    : return $dot.'jpc';
           case IMAGETYPE_JP2    : return $dot.'jp2';
           case IMAGETYPE_JPX    : return $dot.'jpf';
           case IMAGETYPE_SWC    : return $dot.'swc';
           default               : return false;
        }
    }
    
    private function isResizeSafe()
    {
        $estimatedMemory = $this->width * $this->height * 3;
        $memoryLimit = ini_get('memory_limit');
        if ($memoryLimit == '') $memoryLimit = get_cfg_var('memory_limit');
        $memoryLimit = $this->convertToBytes($memoryLimit);
        if (function_exists('memory_get_usage'))
            $memoryUsage = memory_get_usage() + 1000000;
        else
            $memoryUsage = 2000000;     
        if (($estimatedMemory + $memoryUsage) > $memoryLimit) return False;
        return True;
    }
    
    private function convertToBytes($val)
    {
        $val = trim($val);
        $last = $val{strlen($val)-1};
        switch($last) 
        {
            case 'k':
            case 'K':
                return (int) $val * 1024;
                break;
            case 'm':
            case 'M':
                return (int) $val * 1048576;
                break;
            default:
                return $val;
        }
    }
    
}

?>
