<?php

class Stato_SendmailTransport implements Stato_IMailTransport
{
    public function send(Stato_Mail $mail)
    {
        $headers = $mail->getHeaders();
        $subject = $headers['Subject'];
        $to = $headers['To'];
        unset($headers['Subject']);
        unset($headers['To']);
        
        $result = mail(implode(',', $to), $subject, $mail->getBody(), $mail->prepareHeaders($headers));
        
        if (!$result) throw new Exception('Unable to send mail');
    }
}