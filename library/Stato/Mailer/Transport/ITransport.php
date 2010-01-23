<?php





interface Stato_Mailer_Transport_ITransport
{
    public function send(Stato_Mailer_Mail $mail);
}