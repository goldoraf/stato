<?php





require_once dirname(__FILE__) . '/../../TestsHelper.php';

class Stato_Webflow_Forms_ErrorsTest extends Stato_Webflow_TestCase
{
    public function setup()
    {
        if (!function_exists('__')) {
            eval('function __($key, $options = array()) {
                return $key;
            }');
        }
        $this->form = new Stato_Webflow_Forms_Form;
        $this->form->subject = new Stato_Webflow_Forms_CharField;
        $this->form->email = new Stato_Webflow_Forms_EmailField;
        $this->errors = new Stato_Webflow_Forms_Errors($this->form);
        $this->errors['subject'] = 'This field is required.';
        $this->errors['email'] = 'Enter a valid value.';
    }
    
    public function testRendering()
    {
        $html = <<<EOT
<ul class="errorlist">
<li><label for="subject">Subject</label> - This field is required.</li>
<li><label for="email">Email</label> - Enter a valid value.</li>
</ul>
EOT;
        $this->assertEquals($html, $this->errors->__toString());
    }
    
    public function testRenderingWithPrefix()
    {
        $html = <<<EOT
<ul class="errorlist">
<li><label for="contact_subject">Subject</label> - This field is required.</li>
<li><label for="contact_email">Email</label> - Enter a valid value.</li>
</ul>
EOT;
        $this->form->setPrefix('contact');
        $this->assertEquals($html, $this->errors->__toString());
    }
    
    public function testRenderingWithLabeledField()
    {
        $html = <<<EOT
<ul class="errorlist">
<li><label for="subject">Subject</label> - This field is required.</li>
<li><label for="email">Email</label> - Enter a valid value.</li>
<li><label for="content">Body</label> - Please enter something.</li>
</ul>
EOT;
        $this->form->content = new Stato_Webflow_Forms_TextField(array('label' => 'Body'));
        $this->errors['content'] = 'Please enter something.';
        $this->assertEquals($html, $this->errors->__toString());
    }
}