<?php

require_once(STATO_CORE_PATH.'/common/common.php');

class EncryptionTest extends UnitTestCase
{
    function test_basic()
    {
        $crypt_text = SEncryption::encrypt('text to encrypt');
        $this->assertEqual('text to encrypt', SEncryption::decrypt($crypt_text));
        $this->assertNotEqual(SEncryption::encrypt('another text to encrypt'), SEncryption::encrypt('another text to encrypt'));
    }
}

?>
