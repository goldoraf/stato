<h1>View <?php echo $singular_hm_name; ?></h1>
<p>
    {{= link_to('Edit', array('action' => 'update', 'id' => $this-><?php echo $singular_us_name; ?>->id)); }} | 
    {{= link_to('Back', array('action' => 'index')); }}
</p>
{{ foreach ($this-><?php echo $singular_us_name; ?>->content_attributes() as $attr) : }}
    <p>
        <b>{{= SInflection::humanize($attr->name); }} : </b>
        {{= $this-><?php echo $singular_us_name; ?>->{$attr->name}; }}
    </p>
{{ endforeach; }}
<p>
    {{= link_to('Edit', array('action' => 'update', 'id' => $this-><?php echo $singular_us_name; ?>->id)); }} | 
    {{= link_to('Back', array('action' => 'index')); }}
</p>

