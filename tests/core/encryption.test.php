<?php

require_once(CORE_DIR.'/common/common.php');

class EncryptionTest extends UnitTestCase
{
    function EncryptionTest()
    {
        $this->UnitTestCase('Encryption class tests');
    }
    
    function testBasic()
    {
        $cryptText = Encryption::encrypt('text to encrypt');
        $this->assertEqual('text to encrypt', Encryption::decrypt($cryptText));
        $this->assertNotEqual(Encryption::encrypt('another text to encrypt'), Encryption::encrypt('another text to encrypt'));
    }
}

?>
