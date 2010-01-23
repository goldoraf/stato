<?php





require_once dirname(__FILE__) . '/../TestsHelper.php';

require_once dirname(__FILE__) . '/files/FakeLogger.php';

class Stato_Di_ContextTest extends Stato_TestCase
{
    public function testGet()
    {
        $c = new Stato_Di_Context();
        $c->register('logger', 'FakeLogger', array('/path/to/file.log'));
        $logger = $c->get('logger');
        $this->assertEquals('FakeLogger', get_class($logger));
        $this->assertSame($logger, $c->get('logger'));
        $this->assertEquals('/path/to/file.log', $logger->logFile);
    }
}