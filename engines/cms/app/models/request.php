<?php

class Request extends SActiveRecord
{
    public static $objects;
    
    public function validate_on_create()
    {
        $this->validate_presence_of('name', 'Veuillez préciser votre nom.');
        $this->validate_presence_of('email', 'Veuillez préciser votre courriel.');
        $this->validate_presence_of('subject', 'Veuillez préciser le sujet de votre demande.');
        $this->validate_presence_of('body', 'Veuillez préciser le contenu de votre demande.');
        $this->validate_format_of('email', array('pattern' => 'email', 'message' => 'Le courriel fourni doit être valide.'));
    }
    
    protected function validate_presence_of($attr, $message)
    {
        SValidation::validate_presence($this, $attr, array('message' => $message));
    }
}

?>
