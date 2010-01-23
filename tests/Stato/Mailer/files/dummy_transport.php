<?php





class Stato_Mailer_Transport_Dummy implements Stato_Mailer_Transport_ITransport
{
    public function send(Stato_Mailer_Mail $mail)
    {
        return $mail->__toString();
    }
}