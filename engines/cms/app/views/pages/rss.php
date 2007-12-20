<rss version="2.0">
  <channel>
    <title><?= config_value('site_name'); ?></title>
    <link><?= home_url(); ?></link>
    <description></description>
    <language>fr-fr</language>
    <pubDate><?= SDateTime::now()->to_rss(); ?></pubDate>
    <lastBuildDate><?= SDateTime::now()->to_rss(); ?></lastBuildDate>
    <docs>http://blogs.law.harvard.edu/tech/rss</docs>
    <generator>Stato CMS</generator>
    <managingEditor><?= config_value('webmaster_mail'); ?></managingEditor>
    <webMaster><?= config_value('webmaster_mail'); ?></webMaster>
    <ttl>40</ttl>

    <? foreach ($this->posts as $p) : ?>
    <item>
      <title><?= $p->title; ?></title>
      <description><?= truncate(strip_html($p->content), 300); ?></description>
      <pubDate><?= $p->created_on->to_rss(); ?></pubDate>
      <link><?= actu_url(array(
                            'permalink' => $p->permalink,
                            'day'       => $p->created_on->day,
                            'month'     => $p->created_on->month,
                            'year'      => $p->created_on->year
                        )); ?></link>
      <guid isPermaLink="true"><?= actu_url(array(
                            'permalink' => $p->permalink,
                            'day'       => $p->created_on->day,
                            'month'     => $p->created_on->month,
                            'year'      => $p->created_on->year
                        )); ?></guid>
    </item>
    <? endforeach; ?>
    
  </channel>
</rss>
