<?php
$name = htmlspecialchars(trim($name));
$mail = trim($mail);

//----------------------------------
// Check the lenght of comment, include name + mail
//----------------------------------

if (strlen($name) > 50){
?>

<div class="error_message"><?=$echo['nameLong']; ?></div>

<?
	return;
}

if (strlen($mail) > 50){
?>

<div class="error_message"><?=$echo['mailLong']; ?></div>

<?
	return;
}

if (strlen($comments) > $config_comment_max_long and $config_comment_max_long and $config_comment_max_long != '0'){
?>

<div class="error_message"><?=$echo['commentLong']; ?></div>

<?
	return;
}
?>

<script type="text/javascript">
function setCookie(name, value, expires, path, domain, secure) {
var curCookie = name + "=" + escape(value) +
((expires) ? "; expires=" + expires.toGMTString() : "") +
((path) ? "; path=" + path : "") +
((domain) ? "; domain=" + domain : "") +
((secure) ? "; secure" : "");
document.cookie = curCookie;
}

function getCookie(name) {
var dc = document.cookie;
var prefix = name + "=";
var begin = dc.indexOf("; " + prefix);
if (begin == -1) {
begin = dc.indexOf(prefix);
if (begin != 0) return null;
} else
begin += 2;
var end = document.cookie.indexOf(";", begin);
if (end == -1)
end = dc.length;
return unescape(dc.substring(begin + prefix.length, end));
}

function deleteCookie(name, path, domain) {
if (getCookie(name)) {
document.cookie = name + "=" +
((path) ? "; path=" + path : "") +
((domain) ? "; domain=" + domain : "") +
"; expires=Thu, 01-Jan-70 00:00:01 GMT";
}
}
</script>

<?
// Check Flood Protection
if ($config_flood_time){
	if (flooder($_SERVER['REMOTE_ADDR'], $id)){
?>

<div class="error_message"><?=sprintf($echo['flood'], $config_flood_time); ?></div>

<?
		return;
	}
}

// Check if IP is banned
$blockip = false;

if ($query = $sql->select(array('table' => 'ipban', 'where' => array("ip = $_SERVER[REMOTE_ADDR]")))){
	$blockip = true;

	foreach ($query as $row){
		$sql->update(array(
		'table'	 => 'ipban',
		'where'	 => array("ip = $_SERVER[REMOTE_ADDR]"),
		'values' => array('count' => ($row['count'] + 1))
		));
	}
}

if ($blockip){
?>

<div class="error_message"><?=$echo['sorryYouSuck']; ?></div>

<?
	return;
}

// Check if name is Protected
$is_member = false;
foreach (c_array('users') as $member_db_line){
	$user_arr = explode('|', $member_db_line);

	//if the name is protected
	if (strtolower($user_arr[2]) == strtolower($name) or strtolower($user_arr[4]) == strtolower($name)){
		$is_member = true;

		if ($user_arr[3] != md5x($password) and $user_arr[3] != $password){
			$comments = str_replace(array('"', '\''), array('&quot;', '&#039;'), $comments);
			$name	  = replace_comment('add', str_replace("\n", '', $name));
			$mail	  = replace_comment('add', str_replace("\n", '', $mail));
?>

<div class="error_message">
<form method="post" action=""><?=$echo['password']; ?>
<input type="password" name="password">
<input type="hidden" name="name" value="<?=$name; ?>">
<input type="hidden" name="comments" value="<?=$comments; ?>">
<input type="hidden" name="mail" value="<?=$mail; ?>">
<input type="hidden" name="ip" value="<?=$_SERVER['REMOTE_ADDR']; ?>">
<input type="hidden" name="action" value="addcomment">
<input type="hidden" name="id" value="<?=$id; ?>">
<input type="hidden" name="ucat" value="<?=$ucat; ?>">
<input type="hidden" name="rememberme" value="<?=$rememberme; ?>">
<?=cute_query_string($QUERY_STRING, array('category', 'skip', 'subaction', 'id', 'ucat'), 'post'); ?>
<input type="submit" value="   OK	" class="button">
</form>
</div>

<?
			return;
		}
	}
}

// Check if only members can post comments
if($config_only_registered_comment == 'yes' and !$is_member){
?>

<div class="error_message"><?=$echo['onlyUsers']; ?></div>

<?
	return;
}

//* Wrap long words
if ($config_auto_wrap > 1){
	$comments_arr = explode("\n", $comments);
	foreach ($comments_arr as $line){
		$wraped_comm .= ereg_replace("([^ \/\/]{".$config_auto_wrap."})", "\\1\n", $line)."\n";
	}

	if (strlen($name) > $config_auto_wrap){
		$name = substr($name, 0, $config_auto_wrap).' ...';
	}

	$comments = $wraped_comm;
}

$comments = replace_comment('add', $comments);
$name	  = replace_comment('add', preg_replace("/\n/", '', $name));
$mail	  = replace_comment('add', preg_replace("/\n/", '', $mail));

if (!$name){
?>

<div class="error_message"><?=$echo['nameEmpty']; ?></div>

<?
	return;
}
if ($mail == ' ' or !$mail){
	$mail = 'none';
} else {
	$ok = false;

	if (preg_match('/^[\.A-z0-9_\-]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/', $mail)){
		$ok = true;
	} elseif ($config_allow_url_instead_mail == 'yes' and preg_match('/((http(s?):\/\/)|(www\.))([\w\.]+)([\/\w+\.-?]+)/', $mail)){
		$ok = true;
	} elseif ($config_allow_url_instead_mail != 'yes'){
?>

<div class="error_message"><?=$echo['mailEmpty']; ?></div>

<?
		return;
	} else {
?>

<div class="error_message"><?=$echo['mailWrong']; ?></div>

<?
		$allow_comments = false;
		return;
	}
}

if (!$comments){
?>

<div class="error_message"><?=$echo['commentEmpty']; ?></div>

<?
	return;
}

$time = time() + ($config_date_adjust * 60);

// Add the Comment
$sql->insert(array(
'table'	 => 'comments',
'values' => array(
			'post_id' => $id,
			'date'	  => $time,
			'author'  => $name,
			'mail'	  => $mail,
			'ip'	  => $_SERVER['REMOTE_ADDR'],
			'comment' => $comments
			)
));

$sql->update(array(
'table'	 => 'news',
'where'	 => array("id = $id"),
'values' => array('comments' => count($sql->select(array('table' => 'comments', 'where' => array("post_id = $id")))))
));

if ($config_flood_time){
	$sql->insert(array(
	'table'	 => 'flood',
	'values' => array(
				'post_id' => $id,
				'date'	  => $time,
				'ip'	  => $_SERVER['REMOTE_ADDR']
				)
	));
}

if ($rememberme == 'yes'){
?>

<script type="text/javascript">
var now = new Date();
now.setTime(now.getTime() + 365 * 24 * 60 * 60 * 1000);
setCookie("commentname", "<?=urlencode($name); ?>", now);

<?

	if ($mail == 'none'){
?>

setCookie("commentmail", "", now);

<?
	} else {
?>

setCookie("commentmail", "<?=$mail; ?>", now);

<?
	}
?>

</script>

<?
} else {
?>

<script type="text/javascript">
var now = new Date();
now.setTime(now.getTime() + 365 * 24 * 60 * 60 * 1000);
deleteCookie("commentname");
deleteCookie("commentmail");
deleteCookie("cn_password");
</script>

<?
}
?>

<script type="text/javascript">self.location.href="<?=$_SERVER['HTTP_REFERER']; ?>";</script>

<?
// Email upon comment posting by Slaver (http://ultra-music.com)

if ($config_send_mail_upon_posting == 'yes'){
	foreach ($sql->select(array('table' => 'news', 'where' => array("id = $id"))) as $row){
		$comments = str_replace('<br />', "\n", $comments);
		cute_mail($config_admin_mail, 'New comment by '.$name,
		'URL: '.cute_get_link($row)."\n".
		'Article: '.$row['title']."\n".
		'Name: '.$name."\n".
		'IP: '.$_SERVER['REMOTE_ADDR']."\n".
		'E-mail: '.$mail."\n".
		'Edit comment: '.$config_http_script_dir.
		'/?mod=editcomments&action=editcomment&newsid='.$id.'&comid='.$sql->last_insert_id('comments', '', 'id')."\n".
		'Delete comment: '.$config_http_script_dir.
		'/?mod=editcomments&action=doeditcomment&newsid='.$id.'&delcomid[]='.$sql->last_insert_id('comments', '', 'id').'&deletecomment=yes'."\n\n".
		'Comment:'."\n".
		'------------'."\n".
		$comments
		);
	}
}
?>