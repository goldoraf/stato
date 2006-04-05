<h1>Listing <?= $this->plural_name; ?></h1>
<p><?= link_to('New '.$this->singular_name, array('action' => 'add')); ?></p>
<? if (count($this->{$this->plural_name}) == 0) : ?>
    <p>No data.</p>
<? else : ?>
    <table>
        <tr>
            <? foreach($attributes = $this->{$this->plural_name}[0]->contentAttributes() as $attr) : ?>
                <th><?= SInflection::humanize($attr->name); ?></th>
            <? endforeach; ?>
        </tr>
        <? for ($i = 0; $i < $count = count($this->{$this->plural_name}); $i++) : ?>
            <tr>
                <? foreach($attributes as $attr) : ?>
                    <td><?= truncate($this->{$this->plural_name}[$i]->{$attr->name}); ?></td>
                <? endforeach; ?>
                <td><?= link_to('View', array('action' => 'view', 'id' => $this->{$this->plural_name}[$i]->id)); ?></td>
                <td><?= link_to('Edit', array('action' => 'edit', 'id' => $this->{$this->plural_name}[$i]->id)); ?></td>
                <td><?= link_to('Delete', array('action' => 'delete', 'id' => $this->{$this->plural_name}[$i]->id),
                                          array('confirm' => 'Are you sure ?')); ?></td>
            </tr>
        <? endfor; ?>
    </table>
<? endif; ?>
<p><?= link_to('New '.$this->singular_name, array('action' => 'add')); ?></p>

