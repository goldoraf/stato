<h2>Actualités</h2>
<p id="actions">
    <?= link_to('Nouvelle actu', array('action' => 'create'), array('class' => 'action new')); ?>
</p>
<? if (count($this->posts) == 0) : ?>
    <p>Aucune actu.</p>
<? else : ?>
    <table>
        <tr>
            <th>Titre</th>
            <th>Date</th>
            <th />
        </tr>
        <? foreach ($this->posts as $post) : ?>
            <tr class="<?= cycle(array('even', 'odd'), 'row_class'); ?>">
                <td>
                    <?= ($post->published) ? image_tag('accept') : image_tag('disable'); ?>&nbsp;
                    <?= truncate($post->title, 70); ?>&nbsp;
                    
                </td>
                <td><?= $post->created_on->localize(); ?></td>
                <td>
                    <?= link_to('Preview', array('action' => 'preview', 'id' => $post->id),
                                           array('popup' => true, 'class' => 'action preview')); ?>
                    <?= link_to('Editer', array('action' => 'update', 'id' => $post->id), array('class' => 'action edit')); ?>
                    <?= button_to('Supprimer', array('action' => 'delete', 'id' => $post->id),
                                               array('confirm' => 'Etes vous sûr(e) ?', 'class' => 'action delete')); ?>
                </td>
            </tr>
        <? endforeach; ?>
    </table>
<? endif; ?>

