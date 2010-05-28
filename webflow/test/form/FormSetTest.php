<?php

require_once dirname(__FILE__) . '/../../../test/TestsHelper.php';

if (!function_exists('__')) {
    function __($key, $options = array()) {
        return $key;
    }
}

require_once dirname(__FILE__) . '/../files/forms/contact_form.php';

class SFormSetTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->form = new ContactForm;
    }
    
    public function test_rendering()
    {
        $html = <<<EOF
<p class="required"><label for="contact_details_firstname">Firstname</label><input type="text" name="contact[details][firstname]" id="contact_details_firstname" /></p>
<p><label for="contact_details_lastname">Lastname</label><input type="text" name="contact[details][lastname]" id="contact_details_lastname" /></p>
<p><label for="contact_address_street">Street</label><input type="text" name="contact[address][street]" id="contact_address_street" /></p>
<p><label for="contact_address_city">City</label><input type="text" name="contact[address][city]" id="contact_address_city" /></p>
<p><label for="contact_phones_0_type">Type</label><input type="text" name="contact[phones][][type]" id="contact_phones_0_type" /></p>
<p class="required"><label for="contact_phones_0_number">Number</label><input type="text" name="contact[phones][][number]" id="contact_phones_0_number" /></p>
<p><label for="contact_phones_1_type">Type</label><input type="text" name="contact[phones][][type]" id="contact_phones_1_type" /></p>
<p class="required"><label for="contact_phones_1_number">Number</label><input type="text" name="contact[phones][][number]" id="contact_phones_1_number" /></p>

EOF;
        $this->assertEquals($html, $this->form->render());
    }
    
    public function test_rendering_with_initial_values()
    {
        $html = <<<EOF
<p class="required"><label for="contact_details_firstname">Firstname</label><input type="text" name="contact[details][firstname]" id="contact_details_firstname" value="John" /></p>
<p><label for="contact_details_lastname">Lastname</label><input type="text" name="contact[details][lastname]" id="contact_details_lastname" value="Doe" /></p>
<p><label for="contact_address_street">Street</label><input type="text" name="contact[address][street]" id="contact_address_street" value="4 rue de la paix" /></p>
<p><label for="contact_address_city">City</label><input type="text" name="contact[address][city]" id="contact_address_city" value="Paris" /></p>
<p><label for="contact_phones_0_type">Type</label><input type="text" name="contact[phones][][type]" id="contact_phones_0_type" value="home" /></p>
<p class="required"><label for="contact_phones_0_number">Number</label><input type="text" name="contact[phones][][number]" id="contact_phones_0_number" value="123456789" /></p>
<p><label for="contact_phones_1_type">Type</label><input type="text" name="contact[phones][][type]" id="contact_phones_1_type" value="work" /></p>
<p class="required"><label for="contact_phones_1_number">Number</label><input type="text" name="contact[phones][][number]" id="contact_phones_1_number" value="987654321" /></p>

EOF;
        
        $this->form->set_initial_values(array(
            'details' => array('firstname' => 'John', 'lastname' => 'Doe'),
            'address' => array('street' => '4 rue de la paix', 'city' => 'Paris'),
            'phones' => array(
                array('type' => 'home', 'number' => '123456789'),
                array('type' => 'work', 'number' => '987654321')
            )
        ));
        $this->assertEquals($html, $this->form->render());
    }
    
    public function test_is_valid()
    {
        $data = array(
            'details' => array('firstname' => 'John', 'lastname' => 'Doe'),
            'address' => array('street' => '4 rue de la paix', 'city' => 'Paris'),
            'phones' => array(
                array('type' => 'home', 'number' => '123456789'),
                array('type' => 'work', 'number' => '987654321')
            )
        );
        $this->assertTrue($this->form->is_valid($data));
    }
    
    public function test_is_not_valid()
    {
        $data = array(
            'details' => array('lastname' => 'Doe'),
            'address' => array('street' => '4 rue de la paix', 'city' => 'Paris'),
            'phones' => array(
                array('type' => 'home', 'number' => '123456789'),
                array('type' => 'work')
            )
        );
        $this->assertFalse($this->form->is_valid($data));
    }
}