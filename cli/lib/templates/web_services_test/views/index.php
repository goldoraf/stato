<? foreach ($this->services as $service => $api) : ?>
    <h4>API methods for <?= $service; ?></h4>
    <ul>
        <? foreach ($api->api_methods_names() as $method) : ?>
            <li><?= link_to($service.'.'.$method, 
                            array('action' => 'set_params', 'service' => $service, 'method' => $api->api_method_name($method))); ?></li>
        <? endforeach; ?>
    </ul>
<? endforeach; ?>
