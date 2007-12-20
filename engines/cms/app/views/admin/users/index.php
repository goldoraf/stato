<h2>Utilisateurs</h2>
<p id="actions">
    <?= link_to('Nouvel utilisateur', array('action' => 'create'), array('class' => 'action new')); ?>
</p>
<? if (count($this->users) == 0) : ?>
    <p>No data.</p>
<? else : ?>
    <table>
        <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Dernière connexion</th>
            <th></th>
            <th></th>
        </tr>
        <? foreach ($this->users as $user) : ?>
            <tr class="<?= cycle(array('even', 'odd'), 'row_class'); ?>">
                <td><?= $user->lastname; ?></td>
                <td><?= $user->firstname; ?></td>
                <td><?= $user->email; ?></td>
                <td><?= $user->last_access; ?></td>
                <td><?= link_to('Editer', array('action' => 'edit', 'id' => $user->id), array('class' => 'action edit')); ?></td>
                <td><?= button_to('Supprimer', array('action' => 'delete', 'id' => $user->id),
                                               array('confirm' => 'Etes vous sûr(e) ?', 'class' => 'action delete')); ?></td>
            </tr>
        <? endforeach; ?>
    </table>
<? endif; ?>

