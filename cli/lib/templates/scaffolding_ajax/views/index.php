<h1>Listing <?php echo $plural_hm_name; ?></h1>
<p>{{= link_to('New <?php echo $singular_hm_name; ?>', array('action' => 'create')); }}</p>
<div id="table">
    {{= $this->render_partial('objects_list'); }}
</div>
<p>{{= link_to('New <?php echo $singular_hm_name; ?>', array('action' => 'create')); }}</p>

