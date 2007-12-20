<div id="form">
    <p><label for="centre_exam_nom">Nom</label>
    <?= text_field('centre_exam', 'nom', $this->centre_exam, array('size' => 80)); ?></p>
    <p><label for="centre_exam_tel">Tel</label>
    <?= text_field('centre_exam', 'tel', $this->centre_exam); ?></p>
    <p><label for="centre_exam_fax">Fax</label>
    <?= text_field('centre_exam', 'fax', $this->centre_exam); ?></p>
    <p><label for="centre_exam_site">Site</label>
    <?= text_field('centre_exam', 'site', $this->centre_exam); ?></p>
    <p><label for="centre_exam_email">Email</label>
    <?= text_field('centre_exam', 'email', $this->centre_exam); ?></p>
    <p><label for="centre_exam_adresse">Adresse</label>
    <?= text_area('centre_exam', 'adresse', $this->centre_exam); ?></p>
    <p><label for="centre_exam_code_pos">Code pos</label>
    <?= text_field('centre_exam', 'code_pos', $this->centre_exam); ?></p>
    <p><label for="centre_exam_ville">Ville</label>
    <?= text_field('centre_exam', 'ville', $this->centre_exam); ?></p>
    <p><label for="centre_exam_pays">Pays</label>
    <?= text_field('centre_exam', 'pays', $this->centre_exam); ?></p>
</div>
