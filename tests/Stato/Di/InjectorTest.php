<?php

namespace Stato\Di;

use Stato\TestCase;

require_once __DIR__ . '/../TestsHelper.php';

require_once __DIR__ . '/files/FakeLogger.php';
require_once __DIR__ . '/files/CreditCardProcessor.php';
require_once __DIR__ . '/files/BillingService.php';

class InjectorTest extends TestCase
{
    public function testInjection()
    {
        $c = new Context();
        $c->register('logger', 'FakeLogger', array('/path/to/file.log'));
        $c->register('processor', 'CreditCardProcessor');
        $i = new Injector($c);
        $service = $i->build('BillingService');
        $this->assertEquals('BillingService', get_class($service));
    }
}