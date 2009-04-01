<?php

require_once dirname(__FILE__) . '/../../../test/tests_helper.php';

require_once dirname(__FILE__) . '/../webservice.php';

class SpecialMockRequest extends SRequest
{
    public $raw_post_data;
    
    public function raw_post_data()
    {
        return $this->raw_post_data;
    }
}

class WsUser extends SWebServiceStruct
{
    public function __construct()
    {
        $this->add_member('login', 'string');
        $this->add_member('pwd', 'string');
        $this->add_member('admin', 'boolean');
    }
}

class UserAPI extends SWebServiceAPI
{
    public function __construct()
    {
        $this->add_api_method('hello_world', null, array('string'));
        $this->add_api_method('new_user1', array('string', 'string'), 'WsUser');
        $this->add_api_method('new_user2', array('login' => 'string', 'pwd' => 'string'), 'WsUser');
        $this->add_api_method('new_user3', array(array('login' => 'string', 'pwd' => 'string')), 'WsUser');
        $this->add_api_method('add_users', array(array('User')), array('integer'));
        $this->add_api_method('del_users', array(array('integer')), array('boolean'));
        $this->add_api_method('get_a_fault', null, null);
    }
}

class UserService extends SWebService
{
    public function hello_world()
    {
        return 'Hello world';
    }
    
    public function new_user1()
    {
        $user = new WsUser();
        $user->login = $this->params[0];
        $user->pwd   = $this->params[1];
        $user->admin = false;
        return $user;
    }
    
    public function new_user2()
    {
        $user = new WsUser();
        $user->login = $this->params['login'];
        $user->pwd   = $this->params['pwd'];
        $user->admin = false;
        return $user;
    }
    
    public function new_user3()
    {
        $user = new WsUser();
        $user->login = $this->params[0]['login'];
        $user->pwd   = $this->params[0]['pwd'];
        $user->admin = false;
        return $user;
    }
    
    public function del_users()
    {
         return $this->params[0] == array(1,2,3);
    }
    
    public function get_a_fault()
    {
        throw new SWebServiceFault('ERROR', 42);
    }
}

class ApiController extends SActionController
{
    protected function log_processing() {}
    protected function log_benchmarking() {}
    protected function rescue_action($exception) { throw $exception; }
    
    private $invocator;
 
    protected function initialize()
    {
        $this->invocator = new SWebServiceInvocator($this->request);
        $this->invocator->add_web_service('user', new UserService());
    }
    
    public function xmlrpc()
    {
        $response = $this->invocator->invoke('xmlrpc');
        $this->send_data($response, array('type' => 'text/xml', 'disposition' => 'inline'));
    }
}

class XmlRpcInvocationTest extends StatoTestCase
{
    public function testBasic()
    {
        $this->assertEquals('Hello world', $this->do_method_call('user.helloWorld'));
        $this->assertEquals(array('login' => 'jdoe', 'pwd' => 'test', 'admin' => false),
                           $this->do_method_call('user.newUser1', array('jdoe', 'test')));
        $this->assertEquals(array('login' => 'jdoe', 'pwd' => 'test', 'admin' => false),
                           $this->do_method_call('user.newUser2', array('jdoe', 'test')));
        $this->assertEquals(array('login' => 'jdoe', 'pwd' => 'test', 'admin' => false),
                           $this->do_method_call('user.newUser2', array('login' => 'jdoe', 'pwd' => 'test')));
        $this->assertEquals(array('login' => 'jdoe', 'pwd' => 'test', 'admin' => false),
                           $this->do_method_call('user.newUser3', array(array('login' => 'jdoe', 'pwd' => 'test'))));
        $this->assertEquals(true,
                           $this->do_method_call('user.delUsers', array(array(1,2,3))));
    }
    
    public function testFault()
    {
        $this->setExpectedException('SXmlRpcRequestFailedException');
        $this->do_method_call('user.getAFault');
    }
    
    private function do_method_call($method, $params = array())
    {
        $xml_rpc_request = new SXmlRpcRequest($method, $params);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request = new SpecialMockRequest();
        $request->inject_params(array('action' => 'xmlrpc'));
        $request->raw_post_data = $xml_rpc_request->to_xml();
        $tmp = new STempfile();
        SLogger::initialize($tmp->path());
        $c = new ApiController();
        return SXmlRpcClient::decode_response($c->dispatch($request, new SResponse())->body);
    }
}

