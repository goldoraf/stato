<?php

namespace Stato\Mailer\Transport;

use Stato\Mailer\Mail;

class Sendmail implements ITransport
{
    public function send(Mail $mail)
    {
        $to = $mail->getTo();
        $subject = $mail->getSubject();
        
        $result = mail($to, $subject, $mail->getContent(), $mail->getNonMatchingHeaderLines(array('To', 'Subject')));
        
        if (!$result) throw new Exception('Unable to send mail');
    }
}