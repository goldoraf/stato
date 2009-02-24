<?php

class UserMailer extends Stato_Mailer
{
    protected function welcomeMessage($user)
    {
        $mail = new Stato_Mail();
        $mail->addTo($user->mail, $user->name);
        $mail->setBody($this->render('welcome.plain', array('username' => $user->name)));
        return $mail;
    }
    
    protected function greetingsMessage($user)
    {
        $mail = new Stato_Mail();
        $mail->addTo($user->mail, $user->name);
        $mail->setHtmlBody($this->render('greetings.html', array('username' => $user->name)));
        return $mail;
    }
    
    protected function forgotPasswordMessage($user)
    {
        $mail = new Stato_Mail();
        $mail->addTo($user->mail, $user->name);
        $mail->setHtmlBody($this->render('forgot_password.html', array('username' => $user->name)));
        return $mail;
    }
}