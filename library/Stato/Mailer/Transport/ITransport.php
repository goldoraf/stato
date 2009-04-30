<?php

namespace Stato\Mailer\Transport;

use Stato\Mailer\Mail;

interface ITransport
{
    public function send(Mail $mail);
}