<?php

require_once(CORE_DIR.'/view/view.php');

class AssetTagHelperTest extends HelperTestCase
{   
    private $tmpScriptName = null;
    
    public function setUp()
    {
        $this->tmpScriptName = $_SERVER['SCRIPT_NAME'];
        $_SERVER['SCRIPT_NAME'] = '/index.php';
    }
    
    public function tearDown()
    {
        $_SERVER['SCRIPT_NAME'] = $this->tmpScriptName;
    }
    
    public function testImageTag()
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
    
    public function testJsIncludeTag()
    {
        $this->assertDomEqual(
            javascript_include_defaults(),
            '<script src="/js/controls.js" type="text/javascript"></script>
            <script src="/js/dragdrop.js" type="text/javascript"></script>
            <script src="/js/effects.js" type="text/javascript"></script>
            <script src="/js/prototype.js" type="text/javascript"></script>'
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
    
    public function testStylesheetLinkTag()
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

