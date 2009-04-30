<?php

namespace Stato\Mailer\Mime;

use Stato\TestCase;

require_once __DIR__ . '/../../TestsHelper.php';

class EntityTest extends TestCase
{
    public function setup()
    {
        $this->entity = new Entity();
        $this->entity->addHeader('To', 'john@doe.net');
        $this->entity->addHeader('To', 'jane@doe.net');
        $this->entity->addHeader('From', 'root@dummy.net');
        $this->entity->addHeader('Subject', 'test');
    }
    
    public function testGetAllHeaderLines()
    {
        $this->assertEquals("To: john@doe.net, jane@doe.net\nFrom: root@dummy.net\nSubject: test",
                            $this->entity->getAllHeaderLines());
    }
    
    public function testGetMatchingHeaderLines()
    {
        $this->assertEquals("To: john@doe.net, jane@doe.net\nSubject: test",
                            $this->entity->getMatchingHeaderLines(array('To', 'Subject')));
    }
    
    public function testGetNonMatchingHeaderLines()
    {
        $this->assertEquals("To: john@doe.net, jane@doe.net\nSubject: test",
                            $this->entity->getNonMatchingHeaderLines(array('From')));
    }
}