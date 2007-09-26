<?php

SDependencies::require_component('mailer');

/**
 * This component provides a mailer object and a default template for sending email
 * notifications when errors occur in a Stato application.
 *
 * In your environment configuration file, include :
 *     SDependencies::require_component('email_exception_notifier');
 *     EmailExceptionNotifier::$exception_recipients = array('dummy@nowhere.com');
 *
 *     $config->action_controller->exception_notifier = 'EmailExceptionNotifier';
 * 
 */
class EmailExceptionNotifier extends SMailer
{
    public static $sender_adress = '"Exception Notifier" <exception.notifier@default.com>';
    public static $exception_recipients = array();
    public static $email_prefix = "[ERROR]";
    
    public function notify($exception, $request, $session, $controller_name, $action_name)
    {
        $this->to = self::$exception_recipients;
        $this->from = self::$sender_adress;
        $this->subject = self::$email_prefix.' '
            .$controller_name.'::'.$action_name.'() '.get_class($exception);
        
        $this->body = array
        (
            'exception' => $exception,
            'request' => $request,
            'session' => $session,
            'controller_name' => $controller_name,
            'action_name' => $action_name
        );
        
        $this->send($this->prepare_mail());
    }
    
    protected function template_path($template)
    {
        return STATO_CORE_PATH.'/components/email_exception_notifier/views/exception_notification.php';
    }
}

?>