<?php
///////////////////// TEMPLATE Search /////////////////////


$template_active = <<<HTML
<h3>
<a href="{link=home/post}">{title}</a>
[if-logged]<small style="font-size: 10px;"> (<a href="{cutepath}/index.php?mod=editnews&amp;id={id}" target="_blank" title="Edit news">Edit</a>/<a href="{cutepath}/index.php?mod=editnews&amp;action=delete&amp;selected_news[]={id}" target="_blank" title="Delete news">Delete</a>)</small>[/if-logged]
</h3>

<h4>Date: {date} / Author: {author}[catheader] / Category: {category}[/catheader] / Views: {views} [comheader] / <a href="{link=home/post}#comments">Comments: {comments}</a>[/comheader]</h4>

<div align="justify"><p>{short-story}</p>
<p>[full-link]<a href="{link=home/post}">Read more ...</a>[/full-link]</p>
</div>
HTML;


$template_full = <<<HTML

HTML;


$template_comment = <<<HTML

HTML;


$template_form = <<<HTML

HTML;


$template_prev_next = <<<HTML

HTML;


$template_cprev_next = <<<HTML

HTML;


$template_dateheader = <<<HTML

HTML;


///////////////////// TEMPLATE Default /////////////////////
?>