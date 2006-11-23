<?php

class SMailer
{
    protected $template = null;
    protected $mail     = null;
    protected $view     = null;
    protected $parts    = array();
    
    private static $delivery_method = 'php'; // valeurs possibles : php, smtp, sendmail et test (où le mail est loggé)
    private static $defaults       = array
    (
        'charset'     => 'utf-8',
        'content_type' => 'text/plain',
        'mime_version' => '1.0'
    );
    private static $server_settings = array
    (
        'host'     => 'localhost',
        'port'     => 25,
        'domain'   => 'localhost.localdomain', // HELO
        'auth'     => false,
        'username' => null,
        'password' => null
    );
    
    public static function create()
    {
        $args = func_get_args();
        
        if (empty($args))
            throw new SException('Mailer : mail method required.'); // pas top comme msg
        
        $method_name = $args[0];
        unset($args[0]);
        // PHP5 ne gère pas bien l'héritage de fonctions statiques, aussi sommes-nous
        // obligés de préciser que nous voulons instancier la classe ApplicationMailer.
        // Cela contraint l'user à nommer sa classe ApplicationMailer...
        $mailer = new ApplicationMailer($method_name);
        
        // appel de la méthode définie par l'user
        call_user_func_array(array($mailer, $method_name),$args);
        
        return $mailer->prepare_mail();
    }
    
    public static function deliver()
    {
        $args = func_get_args();
        
        if (empty($args))
            throw new SException('Mailer : mail method required.'); // pas top comme msg
            
        if (is_object($args[0]) && get_class($args[0]) == 'SMail') $mail = $args[0];
        else $mail = call_user_func_array(array(self, 'create'), $args);
        
        return self::send($mail);
    }
    
    public function __construct($method_name)
    {
        $this->template = $method_name;
        $this->mail     = new SMail();
        $this->view     = new SActionView();
    }
    
    public function __set($key, $value)
    {
        if (is_array($this->mail->$key)) array_push($this->mail->$key, $value);
        elseif ($key == 'body') $this->mail->body = $this->render_template($value);
        else $this->mail->$key = $value;
    }
    
    public function __get($key)
    {
        return $this->mail->$key;
    }
    
    public function prepare_mail()
    {
        return $this->mail;
    }
    
    protected function render_template($assigns)
    {
        $path = APP_DIR.'/views/mailer/'.$this->template.'.php';
        return $this->view->render($path, $assigns);
    }
    
    private static function send($mail)
    {
        $send_method = self::$delivery_method.'_send';
        return self::$send_method($mail);
    }
    
    // utilise la fonction mail() de PHP
    private static function php_send($mail)
    {
        $headers = $mail->headers();
        
        $subject = $headers['Subject'];
        $to = $headers['To'];
        unset($headers['Subject']);
        unset($headers['To']);
        
        $headers = implode("\n", $headers);
        
        return @mail($to, $subject, $mail->body, $header);
    }
    
    private static function sendmail_send($mail) {}
    
    private static function smtp_send($mail) {}
}

?>
