<?php





require_once dirname(__FILE__) . '/../../TestsHelper.php';

class Stato_Mailer_Mime_PartTest extends Stato_TestCase
{
    public function testTextPlainPart()
    {
        $part = <<<EOT
Content-Type: text/plain; charset="UTF-8"
Content-Transfer-Encoding: 8bit

test
EOT;
        $this->assertEquals($part, (string) new Stato_Mailer_Mime_Part('test'));
    }
}