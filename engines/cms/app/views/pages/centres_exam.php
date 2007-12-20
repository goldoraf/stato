<div id="page-content">
    <h2>Centres agréés CCIP</h2>
    <?= form_tag(array('action' => 'centres_exam')); ?>
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
                        <?= $c->ville; ?><br />
                        <?= $c->pays; ?></td>
                </tr>
                <tr>
                    <th class="tel">Tel</th>
                    <td><?= $c->tel; ?></td>
                </tr>
                <? if ($c->fax !== null) : ?>
                    <tr>
                        <th class="fax">Fax</th>
                        <td><?= $c->fax; ?></td>
                    </tr>
                <? endif; ?>
                <? if ($c->email !== null) : ?>
                    <tr>
                        <th class="email">Email</th>
                        <td><?= $c->email; ?></td>
                    </tr>
                <? endif; ?>
                <? if ($c->site !== null) : ?>
                    <tr>
                        <th class="site">Site web</th>
                        <td><?= $c->site; ?></td>
                    </tr>
                <? endif; ?>
            </table>
        <? endforeach; ?>
    <? endif; ?>
</div>
<?= $this->render_partial('secondary_nav', array('root_page' => $this->exams_root_page)); ?>
