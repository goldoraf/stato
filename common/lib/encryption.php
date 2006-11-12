<?php

class SEncryption
{
    private static $cypher = 'blowfish';
    private static $mode   = 'cfb';
    private static $key    = 'this is the key';
    
    public static function encrypt($plain_text)
    {
        $handler = mcrypt_module_open(self::$cypher, '', self::$mode, '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($handler), MCRYPT_RAND);
        mcrypt_generic_init($handler, self::$key, $iv);
        
        $crypt_text = mcrypt_generic($handler, $plain_text);
        mcrypt_generic_deinit($handler);
        return $iv.$crypt_text;
    }
    
    public static function decrypt($crypt_text)
    {
        $handler = mcrypt_module_open(self::$cypher, '', self::$mode, '');
        $iv_size = mcrypt_enc_get_iv_size($handler);
        $iv = substr($crypt_text, 0, $iv_size);
        $crypt_text = substr($crypt_text, $iv_size);
        $plain_text = '';
        if ($iv)
        {
            mcrypt_generic_init($handler, self::$key, $iv);
            $plain_text = mdecrypt_generic($handler, $crypt_text);
            mcrypt_generic_deinit($handler);
        }
        return $plain_text;
    }
}

?>
