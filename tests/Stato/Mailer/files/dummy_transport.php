<?php

namespace Stato\Mailer\Transport;

use Stato\Mailer\Mail;

class Dummy implements ITransport
{
    public function send(Mail $mail)
    {
        return $mail->__toString();
    }
}