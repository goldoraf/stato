<?php

namespace Stato\Mailer\Mime;

use Stato\TestCase;

require_once __DIR__ . '/../../TestsHelper.php';

class PartTest extends TestCase
{
    public function testTextPlainPart()
    {
        $part = <<<EOT
Content-Type: text/plain; charset="UTF-8"
Content-Transfer-Encoding: 8bit

test
EOT;
        $this->assertEquals($part, (string) new Part('test'));
    }
}