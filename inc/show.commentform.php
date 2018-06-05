<?php
$output = $template_form;

if ($is_logged_in){
	$output = str_replace('[if-logged]', '', $output);
	$output = str_replace('[/if-logged]', '', $output);
	$output = preg_replace('/\[not-logged\](.*?)\[\/not-logged\]/si', '', $output);
} else {
	$output = str_replace('[not-logged]', '', $output);
	$output = str_replace('[/not-logged]', '', $output);
	$output = preg_replace('/\[if-logged\](.*?)\[\/if-logged\]/si', '', $output);
}

$output = run_filters('add-comment-box', $output);
$output = str_replace('{smilies}', insertSmilies('short', $config_smilies_line), $output);
$output = str_replace('{cutepath}', $config_http_script_dir, $output);
$output = str_replace('{id}', $id, $output);
$output = str_replace('{title}', $title, $output);
$output = str_replace('{username}', $member['username'], $output);
$output = str_replace('{usermail}', $member['mail'], $output);
$output = str_replace('{password}', $member['password'], $output);

if ($_COOKIE['commentname']){
	$output = str_replace('{savedname}', urldecode($_COOKIE['commentname']), $output);
} else {
	$output = str_replace('{savedname}', '', $output);
}

if ($_COOKIE['commentmail']){
	$output = str_replace('{savedmail}', $_COOKIE['commentmail'], $output);
} else {
	$output = str_replace('{savedmail}', '', $output);
}

// rememberme input
$output = str_replace('{remember}', '<input type="checkbox" id="rememberme" name="rememberme" value="yes" checked>', $output);
// rememberme input
?>

<script type="text/javascript">
function insertext(open, close, spot){
	msgfield = document.forms['comment'].elements['comments'];

	// IE support
	if (document.selection && document.selection.createRange){
		msgfield.focus();
		sel = document.selection.createRange();
		sel.text = open + sel.text + close;
		msgfield.focus();
	}

	// Moz support
	else if (msgfield.selectionStart || msgfield.selectionStart == '0'){
		var startPos = msgfield.selectionStart;
		var endPos = msgfield.selectionEnd;

		msgfield.value = msgfield.value.substring(0, startPos) + open + msgfield.value.substring(startPos, endPos) + close + msgfield.value.substring(endPos, msgfield.value.length);
		msgfield.selectionStart = msgfield.selectionEnd = endPos + open.length + close.length;
		msgfield.focus();
	}

	// Fallback support for other browsers
	else {
		msgfield.value += open + close;
		msgfield.focus();
	}

	return;
}
</script>

<form name="form" method="post" id="comment" action="<?=$PHP_SELF; ?>"><?=$output; ?>
<input type="hidden" name="action" value="addcomment">
<input type="hidden" name="id" value="<?=$id; ?>">
<input type="hidden" name="ucat" value="<?=$ucat; ?>">
<?=$user_post_query; ?>
</form>