<?php
include '../head.php';

// pgt start
// Ńňŕđň ďîäń÷¸ňŕ âđĺěĺíč ăĺíĺđŕöčč ńňđŕíčöű (page generation time aka pgt)
$pgt = new microTimer;
$pgt->start();
// end pgt start

$header = array(
		  'users'	 => 'Users',
		  'search'	 => 'Search',
		  'category' => 'Categories',
		  'archives'  => 'Archives',
);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<meta content="text/html; charset=iso-8859-2" http-equiv="Content-Type">
<link href="<?=$config_http_script_dir; ?>/example/img/style.css" rel="stylesheet" type="text/css" media="screen" />
<link rel="alternate" type="application/rss+xml" title="RSS" href="<?=cute_get_link(array(), 'feed', 'rss'); ?>">
<title>
<?=cn_title(' &laquo; ', true); ?>
</title>
</head>
<body topmargin="0" bgcolor="4B3625">
<table width="600" height="95%" cellspacing="0" cellpadding="0" align="center" bgcolor="#000">
  <tr>
	<td height="95%"> <table width="590" height="100%" align="center" bgcolor="D0CFB4" cellspacing="0" cellpadding="0">
		<tr>
		  <td width="584" height="166" background="<?=$config_http_script_dir; ?>/example/img/logo-back.jpg" valign="top">
			<table width="100%" height="88%" cellspacing="0" cellpadding="0">
			  <tr>
				<td width="280" valign="bottom" class="logo" align="center">
				  <?=$config_home_title; ?>
				<td width="70">&nbsp;
				<td align="center" valign="bottom" class="calendar">
				  <?=cn_calendar(); ?>
			</table>
		  <td rowspan="3" background="<?=$config_http_script_dir; ?>/example/img/design.jpg" width="13">&nbsp;
		<tr>
		  <td height="18"> <table width="100%" cellspacing="0" cellpadding="0" class="nav">
			  <tr>
				<td class="nav" style="color: DAD7B8"><img src="<?=$config_http_script_dir; ?>/example/img/thread.gif" hspace="5" align="left">
				  <a href="<?=$config_http_script_dir; ?>/example">News</a>
				  <!-- Remove index.php/ when using mod_rewrite -->
				  <a href="<?=$config_http_script_dir; ?>/example/index.php/category">Categories</a>
				  <a href="<?=$config_http_script_dir; ?>/example/index.php/users">Users</a>
				  <a href="<?=$config_http_script_dir; ?>/example/index.php/archives">Archives</a>
				  <a href="<?=$config_http_script_dir; ?>/example/index.php/search">Search</a>
				<td><img src="<?=$config_http_script_dir; ?>/example/img/thread.gif">
			</table>
		<tr>
		  <td valign="top"	background="<?=$config_http_script_dir; ?>/example/img/back.jpg">
			<table width="100%" height="122" cellspacing="0" cellpadding="0" background="<?=$config_http_script_dir; ?>/example/img/top-page.jpg">
			  <tr>
				<td class="name"><?=($header[$do] ? $header[$do] : 'News'); ?>
			</table>
			<table width="90%" align="center" cellspacing="0" cellpadding="0">
			  <tr>
				<td class="p"> <br />
<?php
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  Âűáčđŕĺě, ÷ňî číęëóäčňü
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
if ($do == 'search'){
	include $cutepath.'/search.php';
} elseif ($do == 'users'){
	$bgcolor = '#f7f7f7';
	include $cutepath.'/show_users.php';
} elseif ($do == 'category' and !$category){
	echo cn_category();
} elseif ($do == 'archives'){
	echo cn_archives();
} else {
	$number = 7;
	//$sort = array('title', 'ASC');
	include $cutepath.'/show_news.php';
}
?>
				  <br />
			</table>
			<table width="100%" height="194" cellspacing="0" cellpadding="0" background="<?=$config_http_script_dir; ?>/example/img/back-support.jpg">
			  <tr>
				<td	 class="p"> <table width="550" align="right" cellspacing="0" cellpadding="0">
					<tr>
					  <td width="180" valign="top"> <font class="support">Donate</font><br />
						If you want to support this project,
						please donate some money using
						WebMoney: <br /> <br />
						[$]: Z584423619010<br />
						[&euro;]: E489862997841<br />
						[R]: R435030808543
					  <td width="20">&nbsp;
<div align="right"><a href="#">Top</a></div>
					  <td width="35">&nbsp;
					  <td valign="top"><font class="support">Support</font> <div>
						  <ul>
							<li><a href="http://cutenews.ru/">CuteNews.RU</a></li>
							<li><a href="http://cutenews.ru/forum/">Russian Forum</a></li>
							<li><a href="http://english.cutenews.ru/forum">English Forum</a></li>
							<li><a href="http://cutephp.com/">CutePHP Scripts</a></li>
							<li><a href="http://slaver.cutenews.ru/">Slaver</a></li>
							<li><a href="http://lexa.cutenews.ru/">Ë¸őŕ zloy č
							  ęđŕńčâűé</a></li>
							<li><a href="http://swizzer.cutenews.ru/">SwiZZeR</a></li>
						  </ul>
						</div></table></table></table>
  <tr>
	<td><br /> <table class="rights" width="95%"  align="center"cellspacing="0" cellpadding="0">
		<tr>
		  <td valign="top">Powered by <a href="http://cutenews.ru" style="color: F2EFCB"><b>GoodGirl</b></a>
			&copy; 2004-
			<?=date('Y'); ?>
			. <a href="<?=cute_get_link(array(), 'feed', 'rss'); ?>" style="color: F2EFCB"><strong>RSS</strong></a>
		  <td align="right">
			<?
			// ĺńëč âęëţ÷¸í ăëîáŕëüíűé ęýř (global_cache = true), ňî îáđŕůĺíčé ę ÁÄ íîëü
			// âđĺě˙ íŕ ăĺíĺđŕöčţ ńňđŕíčöű ńîâńĺě ęîďĺĺ÷íîĺ, čáî îňęđűâŕĺňń˙ ďîëíŕ˙ ńňŕňčęŕ
			// č, â îáůĺě-ňî, ďîőóé íŕ îáű÷íűé ęýř
			if (!global_cache){
			?>
			Generated in <?=$pgt->stop(); ?> seconds. <br />
			DB queries: <?=$sql->query_count(); ?><br />
			<? } ?>
			<br /> <br /> </table>
</table>
</body>
</html>