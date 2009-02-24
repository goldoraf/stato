<?php

/**
 * Class allowing you to group email sending features of your application
 * 
 * This class allows you to send emails using templates :
 * <code>
 * Stato_Mailer::setTemplateRoot('/path/to/msg/templates');
 * 
 * class UserMailer extends Stato_Mailer
 * {
 *     public function welcomeEmail($user) {
 *         $mail = new Stato_Mail();
 *         $mail->setTo($user->email_address);
 *         $mail->setBody($this->renderMessage('welcome', array('username' => $user->name)));
 *     }
 * }
 * </code>
 * In the mail defined above, the template at /path/to/msg/templates/welcome.php 
 * would be used to render the mail body. Parameters passed as second argument 
 * would be available as variables in the template :
 * <code>
 * Hello <?php echo $username; ?>
 * </code>
 * 
 * By default, mails are sent with the Stato_SendmailTransport class which 
 * uses mail() PHP function, but you can use another transport implementing 
 * the Stato_IMailTransport interface :
 * <code>
 * $transport = new Stato_SmtpTransport();
 * Stato_Mailer::setDefaultTransport($transport);
 * </code>
 *
 * @package Stato
 * @subpackage mailer
 */
class Stato_Mailer
{
    protected static $templateRoot;
    
    protected static $transport;
    
    public static function setTemplateRoot($path)
    {
        self::$templateRoot = $path;
    }
    
    public static function setDefaultTransport(Stato_IMailTransport $transport)
    {
        self::$transport = $transport;
    }
    
    public function __call($methodName, $args)
    {
        if (preg_match('/^send([a-zA-Z0-9_]*)$/', $methodName, $m))
            return $this->send($m[1], $args);
        elseif (preg_match('/^prepare([a-zA-Z0-9_]*)$/', $methodName, $m))
            return $this->prepare($m[1], $args);
        
        throw new Stato_MailException(get_class($this)."::$methodName() method does not exist");
    }
    
    public function prepare($methodName, $args)
    {
        $mail = call_user_func_array(array($this, $methodName), $args);
        if (!($mail instanceof Stato_Mail))
            throw new Stato_MailException('Mailer methods must return instances of Stato_Mail class');
        return $mail;
    }
    
    /**
     * Renders a message template
     * 
     * @param string $templateName
     * @param array $locals
     * @return string
     */
    protected function render($templateName, $locals = array())
    {
        $templatePath = $this->getTemplatePath($templateName);
        extract($locals);
        ob_start();
        include ($templatePath);
        return ob_get_clean();
    }
    
    /**
     * Returns the absolute path of a template (if found)
     * 
     * @param string $templateName
     * @return string
     */
    protected function getTemplatePath($templateName)
    {
        if (file_exists($templateName)) return $templateName;
        
        if (!isset(self::$templateRoot))
            throw new Stato_MailException('Template root not set');
            
        $templatePath = self::$templateRoot.'/'.$templateName.'.php';
        if (!file_exists($templatePath) || !is_readable($templatePath))
            throw new Stato_MailException("Missing template $templatePath");
            
        return $templatePath;
    }
    
    protected function getTransport()
    {
        $transport = (isset(self::$transport)) ? self::$transport : new Stato_SendmailTransport();
    }
}