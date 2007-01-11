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
        $this->add_api_method('new_user', array('login' => 'string', 'pwd' => 'string'), 'User');
    }
}

class UserService extends SWebService
{
    public function new_user()
    {
        $user = new User();
        $user->login = $this->params[0];
        $user->pwd   = $this->params[1];
        $user->admin = false;
        return $user;
    }
    
    public function hello_world()
    {
        return 'Hello world';
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
        $this->assertEqual('Hello world', $this->do_method_call('user.hello_world'));
        $this->assertEqual(array('login' => 'jdoe', 'pwd' => 'test', 'admin' => false),
                           $this->do_method_call('user.new_user', array('jdoe', 'test')));
    }
    
    private function do_method_call($method, $params = array())
    {
        $request = new MockRequest();
        $request->action = 'xmlrpc';
        $request->raw_post_data = SXmlRpcClient::encode_request($method, $params);
        $c = new ApiController();
        return SXmlRpcClient::decode_response($c->process($request, new MockResponse())->body);
    }
}

?>
