<?php

class ApplicationMailer extends Mailer
{
    public function signupNotification($user)
    {
        $this->to = $user->email;
        $this->subject = "Création d'un compte Opopope";
        $this->body = array('user' => $user);
        $this->from = "system@opopope.net";
    }
}

class Mailer
{
    /*public $to = array();
    public $from = "";
    public $cc = array();
    public $subject = "";
    public $body = null;
    public $charset = "utf-8";
    public $contentType = "text/plain";
    public $mimeVersion = "1.0";*/
    
    public $body = Null;
    
    protected $template = Null;
    protected $mail     = Null;
    protected $parts    = array();
    
    private static $instance = Null;
    
    private static $templatePath   = 'views/mailing';
    private static $deliveryMethod = 'mail'; // valeurs possibles : mail, smtp, sendmail et test (où le mail est loggé)
    private static $defaults       = array
    (
        'charset'     => 'utf-8',
        'contentType' => 'text/plain',
        'mimeVersion' => '1.0'
    );
    private static $serverSettings = array
    (
        'host'     => 'localhost',
        'port'     => 25,
        'domain'   => 'localhost.localdomain', // HELO
        'auth'     => false,
        'username' => Null,
        'password' => Null
    );
    
    public function __construct($methodName)
    {
        $this->template = strtolower($methodName);
        $this->mail     = new Mail();
        foreach(self::$defaults as $header => $value) $this->mail->$header = $value;
    }
    
    public function __set($header, $value)
    {
        $this->mail->$header = $value;
    }
    
    public function __get($header)
    {
        return $this->mail->$header;
    }
    
    public function part($params)
    {
        $this->parts[] = new Part($params);
    }
    
    public function attachment($params, $block)
    {
        
    }
    
    public static function create()
    {
        $args = func_get_args();
        if (empty($args))
        {
            throw new Exception('Mailer : mail method required.'); // pas top comme msg
        }
        
        $methodName = $args[0];
        unset($args[0]);
        // PHP5 ne gère pas bien l'héritage de fonctions statiques, aussi sommes-nous
        // obligés de préciser que nous voulons instancier la classe ApplicationMailer.
        // Cela contraint l'user à nommer sa classe ApplicationMailer...
        if (self::$instance == null) self::$instance = new ApplicationMailer($methodName);
        
        // appel de la méthode définie par l'user
        call_user_func_array(array(self::$instance, $methodName),$args);
        
        if (!is_string(self::$instance->body))
        {
        
        }
    }
    
    public static function deliver()
    {
        if (self::$instance == null) self::create();
        
        print_r(self::$instance);
    }
    
    protected function send()
    {
    
    }
    
    // utilise la fonction mail() de PHP
    private function phpSend($mail) {}
    
    private function sendmailSend($mail) {}
    
    private function smtpSend($mail) {}
}

class Mail
{
    private $headersValues    = array();
    private $headersAccessors = array();
    
    private static $headersMap = array
    (
        'to'          => 'To',
        'cc'          => 'Cc',
        'bcc'         => 'Bcc',
        'date'        => 'Date',
        'from'        => 'From',
        'replyTo'     => 'Reply-To',
        'subject'     => 'Subject',
        'mimeVersion' => 'Mime-Version',
        'contentType' => 'Content-Type'
    );
    
    public function __construct()
    {
        $this->headersAccessors = array_keys(self::$headersMap);
    }
    
    public function __set($header, $value)
    {
        if (in_array($header, $this->headersAccessors))
        {
            $this->headersValues[self::$headersMap[$header]] = $value;
        }
    }
    
    public function __get($header)
    {
        if (in_array($header, $this->headersAccessors))
        {
            return $this->headersValues[self::$headersMap[$header]];
        }
    }
    
    // taken from phpmailer
    private function RFCDate()
    {
        $tz = date("Z");
        $tzs = ($tz < 0) ? "-" : "+";
        $tz = abs($tz);
        $tz = ($tz/3600)*100 + ($tz%3600)/60;
        $result = sprintf("%s %s%04d", date("D, j M Y H:i:s"), $tzs, $tz);

        return $result;
    }
    
    private function fixEOL($str)
    {
        $str = str_replace("\r\n", "\n", $str);
        $str = str_replace("\r", "\n", $str);
        $str = str_replace("\n", $this->LE, $str);
        return $str;
    }
    
    private function wrapText($str)
    {
    
    }
}

class Part
{
    private $contentType = "";
    
}


class User
{
    public $email = 'test@test.com';
}

ApplicationMailer::create('signupNotification', new User());
ApplicationMailer::deliver();

?>
