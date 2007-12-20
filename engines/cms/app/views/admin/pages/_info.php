<table>
    <tr>
        <th>Titre</th><td><?= $this->page->title; ?></td>
    </tr>
    <tr>
        <th>Chemin</th><td><?= $this->page->full_path; ?></td>
    </tr>
</table>
<?= truncate($this->page->content, 300); ?>
<p>
    <?= link_to('Editer', array('action' => 'update', 'id' => $this->page->id), array('class' => 'action edit')); ?>
    <?= button_to('Supprimer', array('action' => 'delete', 'id' => $this->page->id),
                               array('confirm' => 'Etes vous sûr(e) ?', 'class' => 'action delete')); ?>
</p>
