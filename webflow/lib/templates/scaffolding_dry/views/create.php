<h1>New <?php echo $singular_hm_name; ?></h1>
<p>{{= link_to('Back', array('action' => 'index')); }}</p>
{{= error_message_for('<?php echo $singular_us_name; ?>', $this-><?php echo $singular_us_name; ?>); }}
{{= form('<?php echo $singular_us_name; ?>', $this-><?php echo $singular_us_name; ?>, array('action' => 'create')); }}
<p>{{= link_to('Back', array('action' => 'index')); }}</p>
