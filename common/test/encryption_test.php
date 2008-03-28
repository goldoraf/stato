<?php

class EncryptionTest extends PHPUnit_Framework_TestCase
{
    function test_basic()
    {
        $crypt_text = SEncryption::encrypt('text to encrypt');
        $this->assertEquals('text to encrypt', SEncryption::decrypt($crypt_text));
        $this->assertNotEquals(SEncryption::encrypt('another text to encrypt'), SEncryption::encrypt('another text to encrypt'));
    }
}

?>
