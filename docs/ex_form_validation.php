<?php
/* dataform : ajout d'une option display_errors
ajout d'un helper : errorsMessages($entity, $options=array('id'=>'xxx', 'class'=>'xxx')) */
function product_form()
{
    $product = new Product();
    
    if ($this->request->method == METHOD_POST)
    {
        $product->populate($this->request->getParam('product'));
        $product->save();
        if (empty($product->errors))
        {
            $this->redirect('index');
            return; // nécessaire ?
        }
    }
    
    $this->response->add('product', $product);
    $this->renderFile($this->getDefaultTemplatePath());
}

// création d'un helper : form($wrapper, $options)
function contact_form()
{
    $form = new ContactForm();
    
    if ($this->request->method == METHOD_POST)
    {
        $form->populate($this->request->getParam('contact'));
        $form->validate(); // ces 2 méthodes pourrait être factorisée en 1 : validate($data)
        // mais il ne serait pas forcément logique pour l'user que le form a gardé les data 
        // en mémoire pour réaffichage
        if (empty($form->errors))
        {
            // envoi mail
            
            $this->redirect('index');
        }
    }
    
    $this->response->add('form', $form);
    $this->renderFile($this->getDefaultTemplatePath());
}

?>
