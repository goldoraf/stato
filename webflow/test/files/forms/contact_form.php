<?php

class ContactForm extends SFormSet
{
    public function __construct(array $initial_values = array())
    {
        $this->prefix = 'contact';
        $this->add_form('details', new PersonForm);
        $this->add_form('address', new AddressForm);
        $this->add_multiple_forms('phones', new PhoneForm, 2);
        $this->set_initial_values($initial_values);
    }
}

class PersonForm extends SForm
{
    public function __construct(array $data = null, array $files = null)
    {
        parent::__construct($data, $files);
        $this->firstname = new SCharField(array('required' => true));
        $this->lastname = new SCharField;
    }
}

class AddressForm extends SForm
{
    public function __construct(array $data = null, array $files = null)
    {
        parent::__construct($data, $files);
        $this->street = new SCharField;
        $this->city = new SCharField;
    }
}

class PhoneForm extends SForm
{
    public function __construct(array $data = null, array $files = null)
    {
        parent::__construct($data, $files);
        $this->type = new SCharField;
        $this->number = new SCharField(array('required' => true));
    }
}