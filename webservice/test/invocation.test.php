<?php

require_once(STATO_CORE_PATH.'/webservice/webservice.php');
require_once(STATO_CORE_PATH.'/controller/controller.php');
require_once(STATO_CORE_PATH.'/view/view.php');
require_once(STATO_CORE_PATH.'/mailer/mailer.php');

class User extends SWebServiceStruct
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
        $this->add_api_method('new_user1', array('string', 'string'), 'User');
        $this->add_api_method('new_user2', array('login' => 'string', 'pwd' => 'string'), 'User');
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
        $user = new User();
        $user->login = $this->params[0];
        $user->pwd   = $this->params[1];
        $user->admin = false;
        return $user;
    }
}

class ApiController extends SActionController
{
    protected function initialize()
    {
        $this->add_web_service('user', new UserService());
    }
    
    public function xmlrpc()
    {
        $this->invoke_web_service('xmlrpc');
    }
}

class XmlRpcInvocationTest extends StatoTestCase
{
    public function testBasic()
    {
        $this->assertEqual('Hello world', $this->do_method_call('user.helloWorld'));
        $this->assertEqual(array('login' => 'jdoe', 'pwd' => 'test', 'admin' => false),
                           $this->do_method_call('user.newUser1', array('jdoe', 'test')));
    }
    
    private function do_method_call($method, $params = array())
    {
        $xml_rpc_request = new SXmlRpcRequest($method, $params);
        $request = new MockRequest();
        $request->action = 'xmlrpc';
        $request->raw_post_data = $xml_rpc_request->to_xml();
        $c = new ApiController();
        return SXmlRpcClient::decode_response($c->process($request, new MockResponse())->body);
    }
}

?>
