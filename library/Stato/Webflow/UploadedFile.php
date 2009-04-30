<?php

namespace Stato\Webflow;

/**
 * Represents a file upload.
 * 
 * @package Stato
 * @subpackage Webflow
 */
class UploadedFile
{
    const SIZE = 'size';
    const PARTIAL = 'partial';
    const NO_FILE = 'no_file';
    const SYSTEM = 'system';
    
    public $name;
    public $size;
    public $type;
    public $error;
    public $tmp;
    
    protected $originalError;
    
    public function __construct($tmp, $name, $type, $size, $error)
    {
        $this->name = $name;
        $this->tmp = $tmp;
        $this->type = $type;
        $this->size = $size;
        switch ($error) {
            case UPLOAD_ERR_OK:
                $this->error = false;
                break;
            case UPLOAD_ERR_INI_SIZE:
                $this->error = self::SIZE;
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $this->error = self::SIZE;
                break;
            case UPLOAD_ERR_PARTIAL:
                $this->error = self::PARTIAL;
                break;
            case UPLOAD_ERR_NO_FILE:
                $this->error = self::NO_FILE;
                break;
            default:
                $this->error = self::SYSTEM;
        }
        $this->originalError = $error;
    }
    
    /**
     * Moves the uploaded file to a new location after checking that the 
     * file is safe.
     */
    public function move($path)
    {
        return move_uploaded_file($this->tmp, $path);
    }
    
    /**
     * Tells whether the file was actually uploaded via HTTP POST.
     * This is useful to help ensure that a malicious user hasn't tried 
     * to trick your app to gain access to sensible files.
     */
    public function isSafe()
    {
        return is_uploaded_file($this->tmp);
    }
    
    /**
     * Tries to get the real file mimetype. It uses the fileinfo extension if 
     * it is available, or uses the mimetype given by the fileserver.
     */
    public function getMimeType()
    {
        if (!class_exists('finfo', false)) return $this->type;
        $info = new \finfo(FILEINFO_MIME);
        return $info->file($this->tmp);
    }
    
    /**
     * Returns the original error constant given by PHP
     */
    public function getOriginalError()
    {
        return $this->originalError;
    }
}