<div id="contenu">
    <h2>Liste des objets de type '<?php echo $entity_class; ?>'</h2>
    <p><?php echo link_to('Nouvel objet', array('action' => 'add')); ?></p>
    <?php if (empty($entities)) { ?>
        <p>Aucune donnée disponible.</p>
    <?php } else { ?>
        <table>
            <tr><?php foreach($attributes = $entities[0]->contentAttributes() as $header) { ?>
                <th><?php echo ucfirst($header); ?></th>
            <?php } ?></tr>
            <?php for ($i=0; $i<$count = count($entities); $i++) { ?>
            <tr><?php foreach($attributes as $attr) { 
                    $value = $entities[$i]->$attr;
                    if (is_object($value)) $value = $value->__toString();
                    if (strlen($value) > 40) $value = substr($value, 0, 40).'...';
                ?>
                <td><?php echo $value; ?></td>
                <?php } ?>
                <td><?php echo link_to('Voir', array('action' => 'view', 'id' => $entities[$i]->id)); ?></td>
                <td><?php echo link_to('Editer', array('action' => 'edit', 'id' => $entities[$i]->id)); ?></td>
                <td><?php echo link_to('Supprimer', array('action' => 'delete', 'id' => $entities[$i]->id), 
                                                   array('confirm' => 'Êtes-vous sûr de vouloir supprimer cet objet ?')); ?></td>
            </tr>
        <?php } ?>
        </table>
    <?php } ?>
    <p><?php echo link_to('Nouvel objet', array('action' => 'add')); ?></p>
</div>
