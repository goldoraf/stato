<?php

require_once(CORE_DIR.'/common/common.php');

class SRoutesTest extends UnitTestCase
{
    function testRewriteUrl()
    {
        $this->assertEqual(BASE_DIR.'/photos/2005/09/13', 
                           SRoutes::rewriteUrl(array('module'     => 'phototheque',
                                                     'controller' => 'photos',
                                                     'action'     => 'archive',
                                                     'year'  => '2005',
                                                     'month' => '09',
                                                     'day'   => '13')));
    }
}

?>
