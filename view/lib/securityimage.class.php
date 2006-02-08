<?php

class SSecurityImage
{
    public $code     = null;
    public $filename = null;
    
    public $width  = 150;
    public $height = 40;
    public $chars  = 5;
    public $size   = 20;
    public $depth  = 5;
    public $lines  = 30;
    
    private $image  = null;
    private $backgroundColor = null;
    
    public function __construct()
    {
        if (!is_dir(ROOT_DIR.'/public/images/captchas'))
            throw new Exception("SSecurityImage : folder public/images/captchas does not exists.");
    }
    
    public function generate($chars = 5)
    {
        $this->chars = $chars;
        $this->deleteOldFiles();
        
        $this->image = imagecreate($this->width, $this->height);
        //imagecolorallocate($this->image, 255, 255, 255);
        @imagecolorallocate($this->image, rand(0, 255), rand(0, 255), rand(0, 255));
        
        $this->drawGradient();
        $this->drawGrid();
        $this->drawDots();
        $this->generateCode();
        $this->drawCharacters2();
        
        $this->filename = rand(0, 9999).'.png';
        
        imagepng($this->image, ROOT_DIR.'/public/images/captchas/'.$this->filename);
        
        imagedestroy($this->image);
    }
    
    private function generateCode()
    {
        $this->code = '';
        for ($i = 0; $i < $this->chars; $i++)
        {
            $this->code .= chr(rand(65, 90));
        } 
    }
    
    private function drawCharacters()
    {
        $spacing = (int)($this->width / $this->chars);
        $code_chars = str_split($this->code);
        for ($i = 0; $i < strlen($this->code); $i++)
        {
            // select random font
            $font = rand(1, 5);
            
            // select random greyscale colour
            $colour = imagecolorallocate($this->image, rand(0, 128), rand(0, 128), rand(0, 128));
            
            // write text to image
            imagestring($this->image, $font, $spacing / 3 + $i * $spacing, 
                        ($this->height - imagefontheight($font)) / 2, $code_chars[$i], $colour); 
        }
    }
    
    private function drawCharacters2()
    {
        $colour = @imagecolorallocate($this->image, rand(0, 255), rand(0, 255), rand(0, 255));
        $code_chars = str_split($this->code);
        
        for($i = 0, $strlen = strlen($this->code), 
            $p = floor(abs((($this->width - ($this->size * $strlen))/2) - floor($this->size/2)));
            $i < $strlen; $i++, $p += $this->size)
        {
            $font = ROOT_DIR.'/core/view/lib/fonts/stalker1.ttf';
            $d = rand(-8, 8);
            $y = rand( floor($this->height/2) + floor($this->size/2), $this->height - floor($this->size/2) );
            for($b = 0; $b <= $this->depth; $b++)
            {
                imagettftext($this->image, $this->size, $d, $p++, $y++, $colour, $font, $code_chars[$i]);
            }
            @imagettftext($this->image, $this->size, $d, $p, $y, $this->backgroundColor, $font, $code_chars[$i]);
        }
        
        /*imagestring ($this->image, $this->size, floor(abs(((($this->width / 2)-($this->size*strlen($this->code)))/2))), 
                                                  floor(($this->height/2)-($this->size/2)), $this->code, $colour );*/
    }
    
    private function drawLines()
    {
        for ($i = 0; $i < $this->lines; $i++)
        {
             $color = imagecolorallocate($this->image, rand(190, 250), rand(190, 250), rand(190, 250));
             imageline($this->image, rand(0, $this->width), rand(0, $this->height), 
                       rand(0, $this->width), rand(0, $this->height), $color);
        }
    }
    
    private function drawDots()
    {
        for($i = 0; $i < $this->width; $i++)
        {
			imagesetpixel ( $this->image, rand(0, $this->width), rand(0, $this->height), 
                            @imagecolorallocate($this->image, rand(0, 255), rand(0, 255), rand(0, 255)) );
		}
    }
    
    private function drawGradient()
    {
        for($i = 0, $rd = rand(0, 100), $gr = rand(0, 100), $bl= rand(0, 100); $i <= $this->height; $i++)
        {
            $g = @imagecolorallocate($this->image, $rd+=2, $gr+=2, $bl+=2);
            @imageline($this->image, 0, $i, $this->width, $i, $g);
        }
        $this->backgroundColor = $g;
    }
    
    private function drawGrid($size = 2)
    {
        $color = imagecolorallocate($this->image, 0, 0, 0);
        
        for($i = 0, $x = 0, $z = $this->width; $i < $this->width; $i++, $z -= $size, $x += $size)
        {
            @imageline($this->image, $x, 0, $x+10, $this->height, $color);
            @imageline($this->image, $z, 0, $z-10, $this->height, $color);
        }
    }
    
    private function deleteOldFiles()
    {
    
    }
}

?>
