<?php

namespace Stato\Orm;

require_once __DIR__ . '/../TestsHelper.php';

require_once __DIR__ . '/files/relations.php';

class RelationsTest extends TestCase
{
    protected $fixtures = array('companies', 'products');
    
    public function testBasic()
    {
        $this->db->map('Company', 'companies', array(
            'properties' => array(
                'name' => 'name',
                'products' => new Relation('Product')
            )
        ));
        $this->db->map('Product', 'products');
    }
}