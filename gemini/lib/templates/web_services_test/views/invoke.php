<h4>Method Invocation Results for <em><?= $this->service; ?>.<?= $this->method->public_name; ?></em></h4>
<p>
<strong>Return Value:</strong><br />
<pre>
<? print_r($this->return_value); ?>
</pre>
</p>
<p>
<strong>Request XML:</strong><br />
<pre>
<?= htmlentities($this->request_xml); ?>
</pre>
</p>
<p>
<strong>Response XML:</strong><br />
<pre>
<?= htmlentities($this->response_xml); ?>
</pre>
</p>
<?= link_to('Back', array('action' => 'set_params', 'service' => $this->service, 'method' => $this->method->name)); ?>
