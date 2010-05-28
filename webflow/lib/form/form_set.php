<?php

class SFormSet implements IteratorAggregate
{
    protected $forms = array();
    protected $prefix;
    
    public function __set($k, $v)
    {
        throw new SFormException('You cannot add a form instance this way');
    }
    
    public function __get($name)
    {
        if (!isset($this->{$name}))
            throw new SFormException('Form not found:'.$name);
            
        return $this->forms[$name];
    }
    
    public function __isset($name)
    {
        return array_key_exists($name, $this->forms);
    }
    
    public function __toString()
    {
        return $this->render();
    }
    
    public function add_form($name, $form)
    {
        $form->set_prefix($this->get_prefix($name));
        $this->forms[$name] = $form;
    }
    
    public function add_multiple_forms($name, $form, $count)
    {
        $this->forms[$name] = array();
        for ($i = 0; $i < $count; $i++) {
            $f = clone $form;
            $f->set_prefix($this->get_prefix($name, $i));
            $this->forms[$name][$i] = $f;
        }
    }
    
    public function getIterator()
    {
        return new RecursiveIteratorIterator(new RecursiveFormSetIterator($this->forms));
    }
    
    public function set_initial_values(array $initial_values)
    {
        foreach ($initial_values as $k => $v) {
            if (array_key_exists($k, $this->forms)) {
                if (!is_array($this->forms[$k])) {
                    $this->forms[$k]->set_initial_values($v);
                } else {
                    foreach ($this->forms[$k] as $i => $form) {
                        if (array_key_exists($i, $v)) $form->set_initial_values($v[$i]);
                    }
                }
            }
        }
    }
    
    public function is_valid(array $data, array $files = array())
    {
        $valid = true;
        foreach ($this->forms as $k => $form) {
            if (!is_array($form)) {
                $form_data = array_key_exists($k, $data) ? $data[$k] : null;
                $form_files = array_key_exists($k, $files) ? $files[$k] : null;
                if (!$form->is_valid($form_data, $form_files)) $valid = false;
            } else {
                foreach ($form as $i => $f) {
                    $form_data = array_key_exists($k, $data) && array_key_exists($i, $data[$k]) ? $data[$k][$i] : null;
                    $form_files = array_key_exists($k, $files) && array_key_exists($i, $files[$k]) ? $files[$k][$i] : null;
                    if (!$f->is_valid($form_data, $form_files)) $valid = false;
                }
            }
        }
        return $valid;
    }
    
    public function render()
    {
        $html = '';
        foreach ($this->getIterator() as $form) $html.= $form->render()."\n";
        return $html;
    }
    
    protected function get_prefix($name, $count = null)
    {
        if (!isset($this->prefix)) {
            if (is_null($count)) return $name;
            return array($name, $count);
        } else {
            if (is_null($count)) return array($this->prefix, $name);
            return array($this->prefix, $name, $count);
        }
    }
}

class RecursiveFormSetIterator extends RecursiveArrayIterator
{
    public function hasChildren()
    {
        return is_array($this->current());
    }
}