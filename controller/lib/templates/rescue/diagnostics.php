<h1><?= get_class($this->exception)." in {$this->controller_name}::{$this->action_name}()"; ?></h1>
<pre><?= $this->exception->getMessage(); ?></pre>
<p><?= "Exception occured at : ".$this->exception->getFile()." : ".$this->exception->getLine(); ?></p>
<table>
    <caption>Stack trace</caption>
    <tr><th>#</th><th>File</th><th>Line</th><th>Function</th></tr>
    <? foreach ($this->exception->getTrace() as $num => $trace) : ?>
    <tr>
        <td><?= $num + 1; ?></td>
        <td><?= $trace['file']; ?></td>
        <td><?= $trace['line']; ?></td>
        <td><?= $trace['class'].$trace['type'].$trace['function']; ?></td>
    </tr>
    <? endforeach; ?>
</table>
