<h4>Method Invocation Details for <em><?= $this->service_name; ?>.<?= $this->method->public_name; ?></em></h4>
<?= form_tag(array('action' => 'invoke')); ?>
    <?= hidden_field_tag('service', $this->service_name); ?>
    <?= hidden_field_tag('method', $this->method->public_name); ?>
    
    <? if ($this->method->expects !== null) : ?>
    
        <strong>Method Parameters:</strong><br />
        <? foreach ($this->method->expects as $k => $type) : ?>
            <p>
                <label for="method_params[<?= $k; ?>]"><?= method_parameter_label($k, $type); ?> (<?= $type; ?>)</label>
                <?= method_parameter_input_fields($this->method, $type, 'method_params', $k); ?>
            </p>
        <? endforeach; ?>
    
    <? endif; ?>
    
    <?= submit_tag('Invoke'); ?>
    <?= link_to('Back', array('action' => 'index')); ?>
</form>
