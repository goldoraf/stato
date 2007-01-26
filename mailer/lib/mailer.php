<?php

class SMailer
{
    public $to = array();
    public $cc = array();
    public $bcc = array();
    public $from = null;
    public $reply_to = null;
    public $subject = null;
    public $body = null;
    public $content_type = 'text/plain';
    public $attachments = array();
    public $parts = array();
    
    protected $template = null;
    protected $mail     = null;
    protected $view     = null;
    
    public static $eol = "\r\n";
    public static $line_length = 74;
    
    private static $delivery_method = 'php';
    
    public static function create()
    {
        $args = func_get_args();
        
        if (empty($args))
            throw new Exception('Mailer : mail method required.'); // pas top comme msg
        
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
            throw new Exception('Mailer : mail method required.'); // pas top comme msg
            
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
    
    public function prepare_mail()
    {
        foreach (array('to', 'cc', 'bcc') as $rec_type)
        {
            $method = 'add_'.$rec_type;
            if (!is_array($this->$rec_type)) 
                $this->$rec_type = array($this->$rec_type);
            foreach ($this->$rec_type as $rec) 
                call_user_func_array(array($this->mail, $method), $rec);
        }
        
        if (is_array($this->from))
            $this->mail->set_from($this->from[0], $this->from[1]);
        else
            $this->mail->set_from($this->from);
            
        $this->mail->set_subject($this->subject);
        
        if ($this->body !== null)
        {
            if ($this->content_type == 'text/html')
                $this->mail->set_html_body($this->render_message($this->template, $this->body));
            else
                $this->mail->set_body($this->render_message($this->template, $this->body));
        }
        
        foreach ($this->parts as $params) $this->mail->add_part($params);
        foreach ($this->attachments as $params) $this->mail->add_attachment($params);
        
        return $this->mail;
    }
    
    protected function render_message($template, $assigns)
    {
        $path = STATO_APP_PATH.'/views/mailer/'.$template.'.php';
        return $this->view->render($path, $assigns);
    }
    
    private static function send($mail)
    {
        $transport_class = 'S'.ucfirst(self::$delivery_method).'MailTransport';
        $transport = new $transport_class();
        return $transport->send($mail);
    }
}

?>
