<?php

if (!extension_loaded('gd'))
    throw new SException('GD extension is required for SImage component');

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
    
    public function resize($max_dim='32', $path)
    {
        if (!function_exists('gd_info') || !$this->is_resize_safe()) return False;
        
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
            $th_x = $max_dim;
            $th_y = $max_dim * ((float)$this->height/(float)$this->width);
        }
        else
        {
            $th_x = $max_dim * ((float)$this->width/(float)$this->height);
            $th_y = $max_dim;
        }
        $thumbnail = imagecreatetruecolor($th_x, $th_y);
        imagecopyresampled ($thumbnail, $image, 0, 0, 0, 0 ,$th_x, $th_y, $this->width, $this->height);
        
        if (!imagejpeg($thumbnail, $path)) return False;
        return True;
    }
    
    public function mimetype()
    {
        return image_type_to_mime_type($this->type);
    }
    
    // une fonction image_type_to_extension a été implémentée
    // dans le CVS de PHP. A suivre...
    public function extension($include_dot=true)
    {
        $dot = $include_dot ? '.' : '';
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
    
    private function is_resize_safe()
    {
        $estimated_memory = $this->width * $this->height * 3;
        $memory_limit = ini_get('memory_limit');
        if ($memory_limit == '') $memory_limit = get_cfg_var('memory_limit');
        $memory_limit = $this->convert_to_bytes($memory_limit);
        if (function_exists('memory_get_usage'))
            $memory_usage = memory_get_usage() + 1000000;
        else
            $memory_usage = 2000000;     
        if (($estimated_memory + $memory_usage) > $memory_limit) return False;
        return True;
    }
    
    private function convert_to_bytes($val)
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
