<h2>Centres d'examens</h2>
<p id="actions">
    <?= link_to('Nouveau centre', array('action' => 'create'), array('class' => 'action new')); ?>
    <?= link_to('Importer depuis un fichier', array('action' => 'import'), array('class' => 'action import')); ?>
</p>
<? if (count($this->centre_exams) == 0) : ?>
    <p>Aucun centre.</p>
<? else : ?>
    <?= form_tag(array('action' => 'par_pays')); ?>
        <p>
            <label for="pays">Filtrer par pays</label>
            <?= select_tag('pays', options_for_select($this->pays, $this->params['pays'])); ?>
            <?= submit_tag('Ok'); ?>
            <? if (isset($this->params['pays'])) : ?>
                <?= link_to('Tous les pays', array('action' => 'index')); ?>
            <? endif; ?>
        </p>
    </form>
    <?= pagination_links($this->centre_exams_pages); ?>
    <table>
        <tr>
            <th>Nom</th>
            <!--<th>Tel</th>
            <th>Fax</th>
            <th>Site</th>
            <th>Email</th>
            <th>Adresse</th>
            <th>Code pos</th>-->
            <th>Ville</th>
            <th>Pays</th>
            <th />
        </tr>
        <? foreach ($this->centre_exams as $centre_exam) : ?>
            <tr>
                <td><?= truncate($centre_exam->nom); ?></td>
                <!--<td><?= truncate($centre_exam->tel); ?></td>
                <td><?= truncate($centre_exam->fax); ?></td>
                <td><?= truncate($centre_exam->site); ?></td>
                <td><?= truncate($centre_exam->email); ?></td>
                <td><?= truncate($centre_exam->adresse); ?></td>
                <td><?= truncate($centre_exam->code_pos); ?></td>-->
                <td><?= truncate($centre_exam->ville); ?></td>
                <td><?= truncate($centre_exam->pays); ?></td>
                <td><?= link_to('Editer', array('action' => 'update', 'id' => $centre_exam->id), 
                                          array('class' => 'action edit')); ?>
                    <?= button_to('Supprimer', array('action' => 'delete', 'id' => $centre_exam->id),
                                               array('confirm' => 'Etes vous sûr(e) ?', 'class' => 'action delete')); ?></td>
            </tr>
        <? endforeach; ?>
    </table>
<? endif; ?>

