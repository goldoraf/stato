<h1>Listing <?= $this->plural_name; ?></h1>
<p><?= link_to('New '.$this->singular_name, array('action' => 'create')); ?></p>
<? if (count($this->entities) == 0) : ?>
    <p>No data.</p>
<? else : ?>
    <table>
        <tr>
            <? foreach($attributes = $this->entities[0]->contentAttributes() as $header) : ?>
                <th><?= ucfirst($header); ?></th>
            <? endforeach; ?>
        </tr>
        <? for ($i = 0; $i < $count = count($this->entities); $i++) : ?>
            <tr>
                <? foreach($attributes as $attr) : ?>
                    <td><?= truncate($this->entities[$i]->$attr); ?></td>
                <? endforeach; ?>
                <td><?= link_to('View', array('action' => 'view', 'id' => $this->entities[$i]->id)); ?></td>
                <td><?= link_to('Edit', array('action' => 'update', 'id' => $this->entities[$i]->id)); ?></td>
                <td><?= link_to('Delete', array('action' => 'delete', 'id' => $this->entities[$i]->id),
                                          array('confirm' => 'Are you sure ?')); ?></td>
            </tr>
        <? endfor; ?>
    </table>
<? endif; ?>
<p><?= link_to('New '.$this->singular_name, array('action' => 'create')); ?></p>

