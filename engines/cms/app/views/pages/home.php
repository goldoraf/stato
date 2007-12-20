<div id="column-g">
    <?= $this->render_partial('produits'); ?>
    <div id="etef">
        <?= image_tag('logo_etef'); ?>
        <?= link_to('La version électronique du Test d\'Evaluation de Français', 
                    page_url(array('path' => 'tef/e-tef'))); ?>
        <hr />
    </div>
    <div id="pc">
        <?= image_tag('couv_pc_28.jpg'); ?>
        <?= link_to('Découvrez <strong>Points Communs</strong>, la revue du français à visée professionnelle de la CCIP', 
                    page_url(array('path' => 'ressources/points-communs'))); ?>
        <p>Des extraits gratuits à télécharger !</p>
        <hr />
    </div>
</div>
<div id="column-d">
    <?= $this->render_partial('actus'); ?>
</div>
