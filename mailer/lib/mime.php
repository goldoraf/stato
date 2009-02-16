<?php

class Stato_Mime
{
    const QUOTED_PRINTABLE = 'quoted-printable';
    
    const BASE64 = 'base64';
    
    private static $unprintableChars = "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F\x7F\x80\x81\x82\x83\x84\x85\x86\x87\x88\x89\x8A\x8B\x8C\x8D\x8E\x8F\x90\x91\x92\x93\x94\x95\x96\x97\x98\x99\x9A\x9B\x9C\x9D\x9E\x9F\xA0\xA1\xA2\xA3\xA4\xA5\xA6\xA7\xA8\xA9\xAA\xAB\xAC\xAD\xAE\xAF\xB0\xB1\xB2\xB3\xB4\xB5\xB6\xB7\xB8\xB9\xBA\xBB\xBC\xBD\xBE\xBF\xC0\xC1\xC2\xC3\xC4\xC5\xC6\xC7\xC8\xC9\xCA\xCB\xCC\xCD\xCE\xCF\xD0\xD1\xD2\xD3\xD4\xD5\xD6\xD7\xD8\xD9\xDA\xDB\xDC\xDD\xDE\xDF\xE0\xE1\xE2\xE3\xE4\xE5\xE6\xE7\xE8\xE9\xEA\xEB\xEC\xED\xEE\xEF\xF0\xF1\xF2\xF3\xF4\xF5\xF6\xF7\xF8\xF9\xFA\xFB\xFC\xFD\xFE\xFF";

    /**
     * Checks if the given string contains no unprintable characters
     *
     * @param string $str
     * @return boolean
     */
    public static function isPrintable($str)
    {
        return (strcspn($str, self::$unprintableChars) == strlen($str));
    }
    
    public static function encode($content, $encoding)
    {
        if (!is_resource($content)) {
            $fp = fopen('php://temp/', 'r+');
            fputs($fp, $content);
            rewind($fp);
            return self::encodeStream($fp, $encoding);
        }
        return self::encodeStream($content, $encoding);
    }
    
    public static function encodeStream($stream, $encoding)
    {
        switch ($encoding) {
            case self::BASE64:
                $filter = 'convert.base64-encode';
                break;
            case self::QUOTED_PRINTABLE:
                $filter = 'convert.quoted-printable-encode';
                break;
            default:
                throw new Exception("No stream filter available for {$encoding} encoding");
        }
        $params = array('line-length' => Stato_Mail::$lineLength, 
                        'line-break-chars' => Stato_Mail::$eol);
        $streamFilter = stream_filter_append($stream, $filter, STREAM_FILTER_READ, $params);
        $content = stream_get_contents($stream);
        stream_filter_remove($streamFilter);
        fclose($stream);
        return $content;
    }
}