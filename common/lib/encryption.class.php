<?php

class Encryption
{
    private static $cypher = 'blowfish';
    private static $mode   = 'cfb';
    private static $key    = 'this is the key';
    
    public static function encrypt($plainText)
    {
        $handler = mcrypt_module_open(self::$cypher, '', self::$mode, '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($handler), MCRYPT_RAND);
        mcrypt_generic_init($handler, self::$key, $iv);
        
        $cryptText = mcrypt_generic($handler, $plainText);
        mcrypt_generic_deinit($handler);
        return $iv.$cryptText;
    }
    
    public static function decrypt($cryptText)
    {
        $handler = mcrypt_module_open(self::$cypher, '', self::$mode, '');
        $ivSize = mcrypt_enc_get_iv_size($handler);
        $iv = substr($cryptText, 0, $ivSize);
        $cryptText = substr($cryptText, $ivSize);
        $plainText = '';
        if ($iv)
        {
            mcrypt_generic_init($handler, self::$key, $iv);
            $plainText = mdecrypt_generic($handler, $cryptText);
            mcrypt_generic_deinit($handler);
        }
        return $plainText;
    }
}

?>
