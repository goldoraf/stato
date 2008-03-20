<?php

class SMailer
{
    public $to;
    public $cc;
    public $bcc;
    public $from;
    public $reply_to;
    public $subject;
    public $body;
    public $content_type;
    public $attachments;
    public $parts;
    
    protected $template = null;
    protected $view     = null;
    
    public static $eol = "\r\n";
    public static $line_length = 74;
    public static $delivery_method = 'php';
    
    public function __construct()
    {
        $this->view = new SActionView();
        $this->reset();
    }
    
    public function __call($method, $args)
    {
        if (preg_match('/^deliver_([a-zA-Z0-9_]*)$/', $method, $m))
            return $this->deliver_mail($m[1], $args);
        elseif (preg_match('/^create_([a-zA-Z0-9_]*)$/', $method, $m))
            return $this->create_mail($m[1], $args);
        else
            throw new Exception("Method $method does not exist");
    }
    
    public function create_mail($method_name, $args)
    {
        $this->template = $method_name;
        call_user_func_array(array($this, $method_name), $args);
        return $this->prepare_mail();
    }
    
    public function deliver_mail($method_name, $args)
    {
        return $this->send($this->create_mail($method_name, $args));
    }
    
    public function send($mail)
    {
        $transport_class = 'S'.ucfirst(self::$delivery_method).'MailTransport';
        $transport = new $transport_class();
        return $transport->send($mail);
    }
    
    protected function prepare_mail()
    {
        $mail = new SMail();
        
        foreach (array('to', 'cc', 'bcc') as $rec_type)
        {
            $method = 'add_'.$rec_type;
            if (!is_array($this->$rec_type)) 
                $this->$rec_type = array($this->$rec_type);
            foreach ($this->$rec_type as $rec) 
                call_user_func_array(array($mail, $method), $rec);
        }
        
        if (is_array($this->from))
            $mail->set_from($this->from[0], $this->from[1]);
        else
            $mail->set_from($this->from);
            
        $mail->set_subject($this->subject);
        
        if ($this->body !== null)
        {
            if (preg_match('/^text\/html/', $this->content_type))
                $mail->set_html_body($this->render_message($this->template, $this->body), $this->content_type);
            elseif (!empty($this->content_type))
                $mail->set_body($this->render_message($this->template, $this->body), $this->content_type);
            else
                $mail->set_body($this->render_message($this->template, $this->body));
        }
        
        foreach ($this->parts as $params) $mail->add_part($params);
        foreach ($this->attachments as $params) $mail->add_attachment($params);
        
        $this->reset();
        
        return $mail;
    }
    
    protected function render_message($template, $assigns)
    {
        return $this->view->render($this->template_path($template), $assigns);
    }
    
    protected function reset()
    {
        $this->to = array();
        $this->cc = array();
        $this->bcc = array();
        $this->from = null;
        $this->reply_to = null;
        $this->subject = null;
        $this->body = null;
        $this->content_type = 'text/plain';
        $this->attachments = array();
        $this->parts = array();
    }
    
    protected function template_path($template)
    {
        $ref = new ReflectionObject($this);
        $base_path = dirname(dirname($ref->getFilename()));
        return $base_path.'/views/'
               .SInflection::underscore(get_class($this))
               ."/$template.php";
    }
}

?>
