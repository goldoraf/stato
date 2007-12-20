<div id="page-content">
    <h2>Centres agréés TEF</h2>
    <?= form_tag(array('action' => 'centres_tef')); ?>
        <p>
            <label for="pays">Choisissez un pays</label>
            <?= select_tag('pays', options_for_select($this->pays, $this->params['pays'])); ?>
            <?= submit_tag('Ok'); ?>
        </p>
    </form>
    <? if (isset($this->centres)) : ?>
        <? foreach ($this->centres as $c) : ?>
            <h4><?= $c->nom; ?></h4>
            <table class="centre">
                <tr>
                    <th class="adresse">Adresse</th>
                    <td><?= nl2br($c->adresse); ?><br />
                        <?= $c->postal(); ?><br />
                        <?= $c->pays; ?></td>
                </tr>
                <tr>
                    <th class="tel">Tel</th>
                    <td><?= $c->tel; ?></td>
                </tr>
                <tr>
                    <th class="fax">Fax</th>
                    <td><?= $c->fax; ?></td>
                </tr>
                <? if ($c->site !== null) : ?>
                    <tr>
                        <th class="site">Site</th>
                        <td><?= link_to('http://'.$c->site, 'http://'.$c->site); ?></td>
                    </tr>
                <? endif; ?>
            </table>
        <? endforeach; ?>
    <? endif; ?>
</div>
<?= $this->render_partial('secondary_nav', array('root_page' => $this->tef_root_page)); ?>
