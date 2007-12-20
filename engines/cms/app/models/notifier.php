<?php

class Notifier extends SMailer
{
    public function request_for_contact($request)
    {
        $this->to = Configuration::value('webmaster_mail');
        $this->from = $request->email;
        $this->subject = 'Demande de contact';
        $this->body = array('request' => $request);
    }
}

?>
