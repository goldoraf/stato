<?php

namespace Stato\Webflow\Plugin;

use Stato\Webflow\Response;
use Stato\Webflow\Request;

require_once __DIR__ . '/../../TestsHelper.php';

require_once 'PHPUnit/Extensions/OutputTestCase.php';

require_once __DIR__ . '/../files/plugins/FooPlugin.php';
require_once __DIR__ . '/../files/plugins/BarPlugin.php';

class BrokerTest extends \PHPUnit_Extensions_OutputTestCase
{

    public function setUp()
    {
        $this->request = new Request();
        $this->response = new Response();
    }

    public function testRegisterPlugin()
    {
        $broker = new Broker();
        $plugin = new \FooPlugin();
        $broker[0] = $plugin;
        $this->assertContains($plugin, $broker);
        unset($broker[0]);
        $broker[] = $plugin;
        $this->assertContains($plugin, $broker);
    }

    public function testUnregisterPlugin()
    {
        $broker = new Broker();
        $plugin = new \FooPlugin();
        $broker[0] = $plugin;
        unset($broker[0]);
        $this->assertNotContains($plugin, $broker);
        $broker[] = $plugin;
        $broker->unregisterPlugin($plugin);
        $this->assertNotContains($plugin, $broker);
    }

    public function testPluginAlreadyExist() 
    {
        $broker = new Broker();
        $plugin = new \FooPlugin();
        $broker[0] = $plugin;
        $this->setExpectedException(__NAMESPACE__ . '\PluginAlreadyExist');
        $broker[1] = $plugin;
        unset($broker[0]);
    }

    public function testPluginIndexAlreadyExist()
    {
        $broker = new Broker();
        $plugin = new \FooPlugin();
        $plugin2 = new \BarPlugin();
        $broker[0] = $plugin;
        $this->setExpectedException(__NAMESPACE__ . '\PluginAlreadyExist');
        $broker[0] = $plugin2;
        unset($broker[0]);
    }

    public function testPluginsExecution() 
    {
        $broker = new Broker();
        $broker[] = new \FooPlugin();
        $broker[] = new \BarPlugin();
        $broker->setRequest($this->request)->setResponse($this->response);
        $broker->preRouting();
        //  Only Foo should modify the request object
        $this->assertEquals('Foo', $this->request->params['preRouting']);
	$broker->postRouting();       
        //  Only Bar shoul have overwrite the Foo modification of request
        $this->assertEquals('Bar', $this->request->params['postRouting']);
	$broker->preDispatch();
        //  Both Bar and Foo should modify the request object
        $this->assertEquals(array('Foo', 'Bar'), $this->request->params['preDispatch']);       
        // None should have modify this
        $request = clone $this->request;
        $params = clone $this->request->params;
	$broker->postDispatch();       
        $this->assertEquals($request, $this->request);
        $this->assertEquals($params, $this->request->params);
    }
}
