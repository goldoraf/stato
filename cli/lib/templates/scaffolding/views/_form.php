<?php 
foreach (SMapper::retrieve($model_class_name)->content_attributes() as $attr) {
    echo '<p><label for="'.$singular_us_name.'_'.$attr->name.'">'
        .SInflection::humanize($attr->name)."</label>\n"
        .'{{= '.form_helper_for_attribute($attr).'(\''.$singular_us_name.'\', \''.$attr->name.'\', $this->'.$singular_us_name.'); }}</p>'."\n";
} 
?>
