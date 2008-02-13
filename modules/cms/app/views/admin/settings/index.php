<h2>Configuration</h2>
<?= form_tag(array('action' => 'update_settings')); ?>
<h3>Paramètres généraux</h3>
<div id="form">
        
        <p>
          <label for="site_name">Nom du site</label> 
          <input name="setting[site_name]" id="site_name" size="80" type="text" value="<?= config_value('site_name'); ?>" />
        </p>
        <p>
          <label for="limit_post_rss">Lister </label>
          <input name="setting[limit_post_rss]" size="4" id="limit_post_rss" type="text" value="<?= config_value('limit_post_rss'); ?>" /> actualités dans le fil RSS.
        </p>
        <p>
          <label for="limit_post_home_page">Lister </label>
          <input name="setting[limit_post_home_page]" size="4" id="limit_post_home_page" type="text" value="<?= config_value('limit_post_home_page'); ?>" /> actualités sur la page d'accueil.
        </p>
        <p>
          <label for="limit_post_news_page">Lister </label>
          <input name="setting[limit_post_news_page]" size="4" id="limit_post_news_page" type="text" value="<?= config_value('limit_post_news_page'); ?>" /> actualités sur la page actualités.
        </p>
        <p>
          <label for="webmaster_mail">Email du webmestre (pour l'envoi des demandes de renseignements)</label> 
          <input name="setting[webmaster_mail]" id="webmaster_mail" type="text" value="<?= config_value('webmaster_mail'); ?>" />
        </p>
</div>
<?= submit_tag('Enregister'); ?>
<?= end_form_tag(); ?>
