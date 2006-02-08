<?php

require_once(CORE_DIR.'/common/common.php');

class SEncryptionTest extends UnitTestCase
{
    function testBasic()
    {
        $cryptText = SEncryption::encrypt('text to encrypt');
        $this->assertEqual('text to encrypt', SEncryption::decrypt($cryptText));
        $this->assertNotEqual(SEncryption::encrypt('another text to encrypt'), SEncryption::encrypt('another text to encrypt'));
    }
}

?>
