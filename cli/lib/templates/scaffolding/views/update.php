<h1>Editing <?php echo $singular_hm_name; ?></h1>
<p>{{= link_to('Back', array('action' => 'index')); }}</p>
{{= form_tag(array('action' => 'update')); }}
    {{= error_message_for('<?php echo $singular_us_name; ?>', $this-><?php echo $singular_us_name; ?>); }}
    {{= hidden_field('<?php echo $singular_us_name; ?>', 'id', $this-><?php echo $singular_us_name; ?>); }}
    {{= $this->render_partial('form'); }}
    {{= submit_tag('Update'); }}
{{= end_form_tag(); }}
<p>{{= link_to('Back', array('action' => 'index')); }}</p>
