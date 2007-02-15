<?php

require_once(STATO_CORE_PATH.'/view/view.php');

class AssetTagHelperTest extends HelperTestCase
{   
    private $tmp_script_name = null;
    
    public function setUp()
    {
        $this->tmp_script_name = $_SERVER['SCRIPT_NAME'];
        $_SERVER['SCRIPT_NAME'] = '/index.php';
    }
    
    public function tearDown()
    {
        $_SERVER['SCRIPT_NAME'] = $this->tmp_script_name;
    }
    
    public function test_image_tag()
    {
        $this->assertDomEqual(image_tag('stato.png'), '<img alt="Stato" src="/images/stato.png" />');
        $this->assertDomEqual(
            image_tag('stato.png', array('size' => '80x30')),
            '<img alt="Stato" src="/images/stato.png" width="80" height="30" />'
        );
        $this->assertDomEqual(
            image_tag('stato.png', array('alt' => 'Stato framework')),
            '<img alt="Stato framework" src="/images/stato.png" />'
        );
        $this->assertDomEqual(
            image_tag('http://statoproject.com/images/stato.png'),
            '<img alt="Stato" src="http://statoproject.com/images/stato.png" />'
        );
    }
    
    public function test_js_include_tag()
    {
        $this->assertDomEqual(
            javascript_include_defaults(),
            '<script src="/js/prototype.js" type="text/javascript"></script>
            <script src="/js/effects.js" type="text/javascript"></script>
            <script src="/js/controls.js" type="text/javascript"></script>
            <script src="/js/dragdrop.js" type="text/javascript"></script>
            <script src="/js/lowpro.js" type="text/javascript"></script>'
        );
        $this->assertDomEqual(
            javascript_include_tag('sortable'),
            '<script src="/js/sortable.js" type="text/javascript"></script>'
        );
        $this->assertDomEqual(
            javascript_include_tag(array('sortable', 'test.js')),
            '<script src="/js/sortable.js" type="text/javascript"></script>
            <script src="/js/test.js" type="text/javascript"></script>'
        );
    }
    
    public function test_stylesheet_link_tag()
    {
        $this->assertDomEqual(
            stylesheet_link_tag('main'),
            '<link rel="stylesheet" type="text/css" media="screen" href="/styles/main.css" />'
        );
        $this->assertDomEqual(
            stylesheet_link_tag(array('main', 'admin')),
            '<link rel="stylesheet" type="text/css" media="screen" href="/styles/main.css" />
            <link rel="stylesheet" type="text/css" media="screen" href="/styles/admin.css" />'
        );
    }
}

?>
