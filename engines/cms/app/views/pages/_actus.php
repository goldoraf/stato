<div id="actus">
    <h2>Actualités</h2>
    <?= link_to(image_tag('feed_icon'), rss_url(), array('style' => 'float:left;margin-right:5px;')); ?>
    <?= link_to('Fil RSS', rss_url(), array('class' => 'rss-link')); ?><br />
    <?= link_to("Qu'est-ce que c'est ?", page_url(array('path' => 'infos/syndication'))); ?>
    <ul>
        <? foreach ($this->posts as $p) : ?>
            <li>
                <?= link_to($p->title, 
                            actu_url(array(
                                'permalink' => $p->permalink,
                                'day'       => $p->created_on->day,
                                'month'     => $p->created_on->month,
                                'year'      => $p->created_on->year
                            )),
                            array('class' => 'actu-title')
                            ); ?><br />
                <p class="actu-date"><?= $p->created_on->localize(); ?></p>
                <p class="actu-chapeau"><?= $p->teaser; ?></p>
            </li>
        <? endforeach; ?>
    </ul>
</div>
