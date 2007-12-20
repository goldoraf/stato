<h2>Pages</h2>
<p><?= link_to('Nouvelle page', array('action' => 'create'), array('class' => 'action new')); ?></p>
<? if ($this->roots->count() == 0) : ?>
    <p>Aucune page.</p>
<? else : ?>
    <table>
        <tr>
            <th />
            <th>Titre</th>
            <th />
            <th>Date</th>
            <th>En ligne</th>
            <th />
            <th />
        </tr>
        <?= page_tree_view($this->roots); ?>
    </table>
<? endif; ?>
<p><?= link_to('Nouvelle page', array('action' => 'create'), array('class' => 'action new')); ?></p>

