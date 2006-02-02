<div id="contenu">
    <h2>Liste des objets de type '<?= $this->entity_class; ?>'</h2>
    <p><?= link_to('Nouvel objet', array('action' => 'add')); ?></p>
    <? if (count($this->entities) == 0) { ?>
        <p>Aucune donnée disponible.</p>
    <? } else { ?>
        <table>
            <tr><? foreach($attributes = $this->entities[0]->contentAttributes() as $header) { ?>
                <th><?= ucfirst($header); ?></th>
            <? } ?></tr>
            <? for ($i=0; $i<$count = count($this->entities); $i++) { ?>
            <tr><? foreach($attributes as $attr) {
                    $value = $this->entities[$i]->$attr;
                    //if (is_object($value)) $value = $value->__toString();
                ?>
                <td><?= truncate($value); ?></td>
                <? } ?>
                <td><?= link_to('Voir', array('action' => 'view', 'id' => $this->entities[$i]->id)); ?></td>
                <td><?= link_to('Editer', array('action' => 'edit', 'id' => $this->entities[$i]->id)); ?></td>
                <td><?= link_to('Supprimer', array('action' => 'delete', 'id' => $this->entities[$i]->id),
                                                   array('confirm' => 'Êtes-vous sûr de vouloir supprimer cet objet ?')); ?></td>
            </tr>
        <? } ?>
        </table>
    <? } ?>
    <p><?= link_to('Nouvel objet', array('action' => 'add')); ?></p>
</div>
