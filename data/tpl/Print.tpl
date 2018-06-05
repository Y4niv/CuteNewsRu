<?php
///////////////////// TEMPLATE Print /////////////////////


$template_active = <<<HTML

HTML;


$template_full = <<<HTML
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>{title} &laquo; $config_home_title</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<style>
body, td {
	font-family: verdana, arial, sans-serif;
	color: #666;
	font-size: 80%;
}
h1, h2, h3, h4 {
	font-family: verdana, arial, sans-serif;
	color: #666;
	font-size: 100%;
	margin: 0px;
}
.link, .link a {
	color: #0000ff;
	text-decoration: underline;
}
</style>
</head>

<body bgcolor="#FFFFFF" text="#FFFFFF">
<table border="0" width="100%" cellspacing="1" cellpadding="3">
 <tr>
  <td>
   <h3>{title}</h3>
   <p>{full-story}
</table>
<br /><br />
<table border="0" width="100%" cellspacing="0" cellpadding="0">
 <tr>
  <td width="1"><nobr>URL for news &laquo;{title}&raquo;</nobr>
  <td>&nbsp;&nbsp;-
  <td class="link">{link=home/post}
 <tr>
  <td>&laquo;$config_home_title&raquo;
  <td>&nbsp;&nbsp;-
  <td class="link">$config_http_home_url
</table>

</body>
</html>
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