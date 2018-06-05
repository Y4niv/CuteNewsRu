<?php
///////////////////// TEMPLATE Default /////////////////////


$template_active = <<<HTML
<h3>
<a href="{link=home/post}">{title}</a>
[if-logged]<small style="font-size: 10px;"> (<a href="{cutepath}/index.php?mod=editnews&amp;id={id}" target="_blank" title=Edit news">Edit</a> / <a href="{cutepath}/index.php?mod=editnews&amp;action=delete&amp;selected_news[]={id}" target="_blank" title="Delete news">Delete</a>)</small>[/if-logged]
</h3>

<h4>Date: {date} / Author: {author}[catheader] / Category: {category}[/catheader] / Views: {views} [comheader] / <a href="{link=home/post}#comments">Comments: {comments}</a>[/comheader]</h4>

<div align="justify"><p>{short-story}</p>
<p>[full-link]<a href="{link=home/post}">Read more...</a>[/full-link]</p>
</div>
HTML;


$template_full = <<<HTML
<h3>
<a href="{link=home/post}">{title}</a>
[page-link]({pages})[/page-link]
[if-logged]<small style="font-size: 10px;"> (<a href="{cutepath}/index.php?mod=editnews&amp;id={id}" target="_blank" title="Edit news">Edit</a> / <a href="{cutepath}/index.php?mod=editnews&amp;action=delete&amp;selected_news[]={id}" target="_blank" title="Delete news">Delete</a>)</small>[/if-logged]
</h3>

<h4>Date: {date} / Author: {author}[catheader] / Category: {category}[/catheader] / Views: {views} [comheader] / <a href="{link=home/post}#comments">Comments: {comments}</a>[/comheader]</h4>

<div align="justify">
<p>{full-story}</p>
<p><a href="{link=trackback/post}" target="_blank">TrackBack</a></p>
<p><a href="{link=print/post}" target="_blank">Print</a></p>
<p><a href="{link=rss/post}" target="_blank">RSS</a></p>
</div>

<h3>[comheader]Comments: {comments}[/comheader]</h3>

<a name="comments"></a>
HTML;


$template_comment = <<<HTML
<div class="{alternating}" id="comment{comnum}" align="justify"><a href="#comment{comnum}"><b>{comnum}.</b></a> {author} | {date}
[if-logged]<small style="font-size: 10px;"> (<a href="{cutepath}/index.php?mod=editcomments&amp;newsid={id}&amp;comid={comment-id}" target="_blank" title="Edit comments" style="color: #ccc;">Edit</a> / <a href="{cutepath}/index.php?mod=editcomments&amp;action=dodeletecomment&amp;newsid={id}&amp;delcomid[]={comment-id}&amp;deletecomment=yes" target="_blank" title="Delete comments" style="color: #ccc;">Delete</a>)</small>[/if-logged]
<br />
<br />
{comment}
</div>
[answer]<div class="{alternating}"><b>Reply:</b><br />{answer}</div>[/answer]
HTML;


$template_form = <<<HTML
<h3>Leave a comment</h3>
<p>
[if-logged]
<!--
ֿנטגוע, <b><a href="{cutepath}/index.php?mod=options&action=personal" target="_blank">{username}</a></b>
<br /><br />
-->
<input type="hidden" name="name" tabindex="1" value="{username}" />
<input type="hidden" name="mail" tabindex="2" value="{usermail}" />
<input type="hidden" name="password" tabindex="3" value="{password}" />
[/if-logged]
[not-logged]
<label for="name"><b>Name:  </b></label> <input id="name" type="text" size="20" name="name" tabindex="1" value="{savedname}" /><br />
<label for="mail"><b>E-mail:</b> <input id="mail" type="text" size="20" name="mail" tabindex="2" value="{savedmail}" /> <i>(optional)</i></label><br />
[/not-logged]

{smilies}<br />
<textarea cols="50" rows="8" name="comments" tabindex="3"></textarea><br />
<input class="input" type="submit" tabindex="4" name="submit" value="Leave a comment" />
<br />
<label for="rememberme">{remember} Remember?</label>
</p>
<p><noindex>{bbcodes}</noindex></p>
HTML;


$template_prev_next = <<<HTML
<p align=center>[prev-link]<[/prev-link] {pages} [next-link]>[/next-link]</p>
HTML;


$template_cprev_next = <<<HTML
<p align=center>[prev-link]<[/prev-link] {cpages} [next-link]>[/next-link]</p>
HTML;


$template_dateheader = <<<HTML
<h3 class="dateheader">[date-link]{dateheader}[/date-link]</h3>
HTML;


///////////////////// TEMPLATE Default /////////////////////
?>