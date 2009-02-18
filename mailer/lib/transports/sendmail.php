<?php

class Stato_SendmailTransport implements Stato_IMailTransport
{
    public function send(Stato_Mail $mail)
    {
        $to = $mail->getTo();
        $subject = $mail->getSubject();
        $headers = $mail->getHeaders(array('To', 'Subject'));
        
        $result = mail($to, $subject, $mail->getBody(), $mail->prepareHeaders($headers));
        
        if (!$result) throw new Exception('Unable to send mail');
    }
}