<?php

require_once(CORE_DIR.'/common/common.php');

class RoutesTest extends UnitTestCase
{
    function RoutesTest()
    {
        Routes::initialize();
    }
    
    function testConvertRegex()
    {
        $this->assertEqual('#^photos/(?P<id>\d+)$#i', Routes::convertRegex('photos/{id}', array('id' => '\d+')));
    }
    
    function testRewriteUrl()
    {
        $this->assertEqual(BASE_DIR.'/photos/2005/09/13', 
                           Routes::rewriteUrl(array('module'     => 'phototheque',
                                                    'controller' => 'photos',
                                                    'action'     => 'archive',
                                                    'year'  => '2005',
                                                    'month' => '09',
                                                    'day'   => '13')));
    }
}

?>
