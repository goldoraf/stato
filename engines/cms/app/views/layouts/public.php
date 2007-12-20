<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title><?= config_value('site_name'); ?></title>
<meta name="description" content="Site du Fran&ccedil;ais des Affaires et des Professions de la Chambre de Commerce et d'Industrie de Paris (CCIP) pr&eacute;sentant les activit&eacute;s du Centre de Langue de la Direction des Relations Internationales de l'Enseignement : examens, TEF (Test d'&eacute;valuation de Fran&ccedil;ais), formations sur mesure, universit&eacute; d'&eacute;t&eacute;.">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="fr" />
<?= stylesheet_link_tag(array('public')); ?>
<?= stylesheet_link_tag(array('print'), array('media' => 'print')); ?>
<?= css_ie_fix(); ?>
<link rel="alternate" type="application/rss+xml" title="Fil RSS" href="<?= rss_url(); ?>" />
<link rel="icon" href="<?= image_path('favicon.ico'); ?>" type="image/x-icon" />
<script type="text/javascript" language="javascript">
function CCIP_home() {
    document.location.href='http://www.fda.ccip.fr/';
}
</script>
</head>
<body>
<div id="container">
    <div id="wrapper">
        <div id="main">
            <div id="header">
                <div class="bandeau_flash">
                	<object type="application/x-shockwave-flash" data="<?= image_path('bandeau_interne_ccip.swf'); ?>" width="760" height="95">
                	<param name="movie" value="<?= image_path('bandeau_interne_ccip.swf'); ?>"/>
                	</object>
                </div>
                <div class="menu_haut">
                	<form action="" name="recherche">
                    <a href="http://www.ccip.fr/" class="bandeau_haut">ccip.fr</a> <a href="http://www.ccip.fr/index.asp?idlangue=5&idmetapage=9&recherche=+" class="bandeau_haut">Rechercher 
                    sur les sites de la CCIP</a> <a href="http://www.boutique.ccip.fr" target="_blank" title="Ouverture dans une nouvelle fenêtre" class="bandeau_haut">la 
                    boutique </a><a href="http://www.boutique.ccip.fr" target="_blank" title="Ouverture dans une nouvelle fenêtre"><img src="<?= image_path('caddie.gif'); ?>" align="top"></a> 
                    <a href="http://www.ccip.fr/index.asp?idlangue=5&idmetapage=35" class="bandeau_haut">Les march&eacute;s 
                    publics</a> <a href="http://www.ccip.fr/index.asp?idlangue=5&idmetapage=32" class="bandeau_haut">Les 
                    lettres d'information</a> 
                  </form>
                </div>
                <div id="root-menu">
                    <ul>
                        <li><?= cms_link_to_unless_current_page('Accueil', home_url()); ?></li>
                        <li><?= cms_link_to_unless_current_root('TEF', page_url(array('path' => 'tef'))); ?></li>
                        <li><?= cms_link_to_unless_current_root('Examens', page_url(array('path' => 'examens'))); ?></li>
                        <li><?= cms_link_to_unless_current_root('Formations', page_url(array('path' => 'formations'))); ?></li>
                        <li><?= cms_link_to_unless_current_root('Ressources', page_url(array('path' => 'ressources'))); ?></li>
                        <li><?= cms_link_to_unless_current_page('Contact', contact_url()); ?></li>
                    </ul>
                </div>
            </div>
            
            <div id="main-content">
                <div id="intro">Bienvenue sur le site du <h1>Français des Affaires et des Professions</h1> de la <strong>Direction
    des Relations Internationales de l'Enseignement</strong></div>
                <?= $this->layout_content; ?>
                <div style="clear: both"></div>
            </div>
        </div>
        <div id="footer">
            <?= link_to('Nos partenaires', page_url(array('path' => 'infos/nos-partenaires'))); ?> | 
            <?= link_to('Offres d\'emploi', page_url(array('path' => 'infos/offres-demploi'))); ?> | 
            <?= link_to('Mentions légales', page_url(array('path' => 'infos/mentions-legales'))); ?>
        </div>
    </div>
</div>
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-1927954-1";
urchinTracker();
</script>
</body>
</html>
