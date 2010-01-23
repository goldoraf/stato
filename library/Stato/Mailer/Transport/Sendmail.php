<?php





class Stato_Mailer_Transport_Sendmail implements Stato_Mailer_Transport_ITransport
{
    public function send(Stato_Mailer_Mail $mail)
    {
        $to = $mail->getTo();
        $subject = $mail->getSubject();
        
        $result = mail($to, $subject, $mail->getContent(), $mail->getNonMatchingHeaderLines(array('To', 'Subject')));
        
        if (!$result) throw new Stato_Mailer_Transport_Exception('Unable to send mail');
    }
}