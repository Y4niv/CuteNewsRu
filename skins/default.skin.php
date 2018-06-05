<?php
$skin_prefix = '';

if ($mod == 'syscon'){
	$bl = ' onload="switchCell(0);"';
}

if (!function_exists('options_submenu')){
	function options_submenu(){
	global $member;

	    ob_start();
	    $echo = cute_lang('options');
	    include rootpath.'/inc/mod/options.mdu';
	    $options = ob_get_contents();
	    ob_get_clean();

	    $options = strip_tags($options, '<a>');
	    $options = str_replace('&nbsp;', '', $options);
	    $options = explode("\r\n", $options);

	    foreach ($options as $option){
	        if ($option){
	            $result[] = $option;
	        }
	    }

	return @join('<br />', $result);
	}
}

$skin_menu = <<<HTML
<td><a class="nav" href="$PHP_SELF?mod=main">{$echo['nav']['main']}</a>
<td>|
<td><a class="nav" href="$PHP_SELF?mod=addnews">{$echo['nav']['addnews']}</a>
<td>|
<td><a class="nav" href="$PHP_SELF?mod=editnews">{$echo['nav']['editnews']}</a>
<td>|
<td><a class="nav" href="$PHP_SELF?mod=options">{$echo['nav']['options']}</a> <a href="javascript:ShowOrHide('options-submenu', 'plus')" id="plus" onclick="javascript:ShowOrHide('minus')">+</a><a href="javascript:ShowOrHide('options-submenu', 'minus')" id="minus" style="display: none;" onclick="javascript:ShowOrHide('plus')">-</a>
<td>|
<td><a class="nav" href="$PHP_SELF?mod=help">{$echo['nav']['help']}</a>
<td>|
<td><a class="nav" href="$PHP_SELF?action=clearcache">{$echo['nav']['clearcache']}</a>
<td>|
<td><a class="nav" href="$PHP_SELF?action=logout">{$echo['nav']['logout']}</a>
<td>|
<td><a class="nav" href="$config_http_home_url">{$echo['nav']['homepage']}</a>
HTML;

$skin_menu .= '<tr id="options-submenu" style="display: none;">
<td colspan="7">
<td colspan="7">'.options_submenu();

$skin_header = <<<HTML
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head>
<meta http-equiv="content-type" content="text/html; charset={$echo['charset']}">
<title>$config_home_title</title>
<link href="skins/default.css" rel="stylesheet" type="text/css" media="screen">
<script type="text/javascript" src="skins/cute.js"></script>
</head>

<body$bl>
HTML;

$skin_header .= <<<HTML
<table border="0" align="center" cellpadding="2" cellspacing="0">
<tr>
<td class="bborder">
<table border="0" cellpadding="0" cellspacing="0" width="700">
<tr>
<td align="center" height="24" class="main">
<table border="0" cellspacing="0" cellpadding="5">
<tr>
<td>{menu}
</table>
<tr>
<td height="19">
<table border="0" cellpading="0"cellspacing="15" width="100%" height="100%">
<tr>
<td><div class="header"><img border="0" src="skins/images/default/{image-name}.gif" align="absmiddle"> {header-text}</div>
<tr>
<td width="100%" height="100%">
HTML;

$skin_footer = <<<HTML
</table>
<tr>
<td height="24" align="center" class="copyrights">{copyrights}</td>
</table>
</table>

</body>
</html>
HTML;
?>