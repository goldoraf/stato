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
        $user = new User();
        $user->login = $this->params[0];
        $user->pwd   = $this->params[1];
        $user->admin = false;
        return $user;
    }
    
    public function new_user2()
    {
        $user = new User();
        $user->login = $this->params['login'];
        $user->pwd   = $this->params['pwd'];
        $user->admin = false;
        return $user;
    }
    
    public function add_users()
    {
        $count = 0;
        foreach ($this->params[0] as $i => $user)
        {
            $u = new User();
            $u->login = 'login'.$i;
            $u->pwd   = 'pwd'.$i;
            $u->admin = false;
            
            if ($user == $u) $count++;
        }
        return $count;
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
        $this->assertEqual(array('login' => 'jdoe', 'pwd' => 'test', 'admin' => false),
                           $this->do_method_call('user.newUser2', array('jdoe', 'test')));
        $this->assertEqual(array('login' => 'jdoe', 'pwd' => 'test', 'admin' => false),
                           $this->do_method_call('user.newUser2', array('login' => 'jdoe', 'pwd' => 'test')));
        $this->assertEqual(true,
                           $this->do_method_call('user.delUsers', array(array(1,2,3))));
        $this->assertEqual(2,
                           $this->do_method_call('user.addUsers', array(array(
                               array('login' => 'login0', 'pwd' => 'pwd0', 'admin' => false),
                               array('login' => 'login1', 'pwd' => 'pwd1', 'admin' => false)
                           ))));
    }
    
    public function testFault()
    {
        $this->expectException('SXmlRpcRequestFailedException');
        $this->do_method_call('user.getAFault');
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
