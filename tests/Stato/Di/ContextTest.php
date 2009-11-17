<?php

namespace Stato\Di;

use Stato\TestCase;

require_once __DIR__ . '/../TestsHelper.php';

require_once __DIR__ . '/files/FakeLogger.php';

class ContextTest extends TestCase
{
    public function testGet()
    {
        $c = new Context();
        $c->register('logger', 'FakeLogger', array('/path/to/file.log'));
        $logger = $c->get('logger');
        $this->assertEquals('FakeLogger', get_class($logger));
        $this->assertSame($logger, $c->get('logger'));
        $this->assertEquals('/path/to/file.log', $logger->logFile);
    }
}