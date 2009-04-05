<h1>New <?php echo $singular_hm_name; ?></h1>
<p>{{= link_to('Back', array('action' => 'index')); }}</p>
{{= form_tag(array('action' => 'create')); }}
    {{= error_message_for('<?php echo $singular_us_name; ?>', $this-><?php echo $singular_us_name; ?>); }}
    {{= $this->render_partial('form'); }}
    {{= submit_tag('Create'); }}
{{= end_form_tag(); }}
<p>{{= link_to('Back', array('action' => 'index')); }}</p>
