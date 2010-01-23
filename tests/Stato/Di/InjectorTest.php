<?php





require_once dirname(__FILE__) . '/../TestsHelper.php';

require_once dirname(__FILE__) . '/files/FakeLogger.php';
require_once dirname(__FILE__) . '/files/CreditCardProcessor.php';
require_once dirname(__FILE__) . '/files/BillingService.php';

class Stato_Di_InjectorTest extends Stato_TestCase
{
    public function testInjection()
    {
        $c = new Stato_Di_Context();
        $c->register('logger', 'FakeLogger', array('/path/to/file.log'));
        $c->register('processor', 'CreditCardProcessor');
        $i = new Stato_Di_Injector($c);
        $service = $i->build('BillingService');
        $this->assertEquals('BillingService', get_class($service));
    }
}