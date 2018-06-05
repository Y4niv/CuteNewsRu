<?PHP
///////////////////// TEMPLATE rss /////////////////////

$template_active = <<<HTML
<item>
<title><![CDATA[{title}]]></title>
<link>{link=home/post}</link>
<description><![CDATA[{short-story}]]></description>
<category><![CDATA[{category}]]></category>
<pubDate>{date=r}</pubDate>
</item>
HTML;

$template_full = <<<HTML
<title><![CDATA[{title}]]></title>
<link>{link=home/post}</link>
<description><![CDATA[{short-story}]]></description>
HTML;

$template_comment = <<<HTML
<item>
<author><![CDATA[{author}]]></author>
<link>{link}#comment{comnum}</link>
<description><![CDATA[{comment}]]></description>
<pubDate>{date=r}</pubDate>
</item>
HTML;
?>
