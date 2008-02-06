<h1>Listing <?php echo $plural_hm_name; ?></h1>
<p>{{= link_to('New <?php echo $singular_hm_name; ?>', array('action' => 'create')); }}</p>
{{ if (count($this-><?php echo $plural_us_name; ?>) == 0) : }}
    <p>No data.</p>
{{ else : }}
    <table>
        <tr>
            <?php 
            foreach (SMapper::retrieve($model_class_name)->content_attributes() as $attr) {
                echo "<th>".SInflection::humanize($attr->name)."</th>\n            ";
            } 
            ?></tr>
        {{ foreach ($this-><?php echo $plural_us_name; ?> as $<?php echo $singular_us_name; ?>) : }}
            <tr>
                <?php 
                foreach (SMapper::retrieve($model_class_name)->content_attributes() as $attr) {
                    if ($attr->type == 'string' || $attr->type == 'text') {
                        echo "<td>{{= truncate($".$singular_us_name."->".$attr->name."); }}</td>\n                ";
                    } else {
                        echo "<td>{{= $".$singular_us_name."->".$attr->name."; }}</td>\n                ";
                    }
                } ?><td>{{= link_to('View', array('action' => 'view', 'id' => $<?php echo $singular_us_name; ?>->id)); }}</td>
                <td>{{= link_to('Edit', array('action' => 'update', 'id' => $<?php echo $singular_us_name; ?>->id)); }}</td>
                <td>{{= link_to('Delete', array('action' => 'delete', 'id' => $<?php echo $singular_us_name; ?>->id),
                                         array('confirm' => 'Are you sure ?')); }}</td>
            </tr>
        {{ endforeach; }}
    </table>
{{ endif; }}
<p>{{= link_to('New <?php echo $singular_hm_name; ?>', array('action' => 'create')); }}</p>

