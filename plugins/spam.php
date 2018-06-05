<?php
/*
Plugin Name:	Spam filter
Plugin URI:		http://cutenews.ru
Description:	Create a list with words and don't allow comments which contain these words.
Version:		0.1
Application:	CuteNews
Author:			&#1051;&#1105;&#1093;&#1072; zloy &#1080; &#1082;&#1088;&#1072;&#1089;&#1080;&#1074;&#1099;&#1081;
Author URI:		http://lexa.cutenews.ru
*/


add_filter('news-allow-addcomment', 'spam_filter');

add_filter('cutenews-options', 'spam_AddToOptions');
add_action('plugin-options', 'spam_CheckAdminOptions');

function spam_filter($allow){
global $name, $mail, $comments;

	$barword = new PluginSettings('BarWord');

	if ($comments){
		foreach($barword->settings as $bad){
			if (preg_match("/$bad/i", strtolower($comments))){$allow = false;}
		}
	}

return ($allow ? true : false);
}

function spam_AddToOptions($options){
global $PHP_SELF;

	$options[] = array(
		'name'		=> 'Spam filter',
		'url'		=> $PHP_SELF.'?mod=options&amp;action=spam',
		'access'	=> '1',
	);

return $options;
}

function spam_CheckAdminOptions(){
	if ($_GET['action'] == 'spam'){spam_AdminOptions();}
}

function spam_AdminOptions(){
global $PHP_SELF;

	echoheader('options', 'Spam');

	$barword = new PluginSettings('BarWord');

	$buffer = '<table border=0 cellpading=0 cellspacing=0 width="645">
			  <table border=0 cellpading=0 cellspacing=0 width="645" >
			  <form method=post action="'.$PHP_SELF.'?mod=options&amp;action=spam">
			  <td width=321 height="33"><b>Add a word</b>
			  <table border=0 cellpading=0 cellspacing=0 width=379	class="panel" cellpadding="7" >
			  <tr>
			  <td width=79 height="25">&nbsp;Word:
			  <td width=300 height="25">
			  <input type="text" name="add_badword">&nbsp;&nbsp;<input type="submit" value="Add to list">
			  </tr>
			  </form>
			  </table>

	<tr>
	<td width=654 height="11">
		<img height=20 border=0 src="skins/images/blank.gif" width=1>
	</tr><tr>
	<td width=654 height=14>
	<b>Spam list</b>
	</tr>
	<tr>
	<td width=654 height=1>
  <table width=641 height=100% cellspacing=2 cellpadding=2>
	<tr>
	  <td width=260 class="panel"><b>Word</b></td>
	  <td width=140 class="panel">&nbsp;<b>Action</b></td>
	</tr>';

	if ($words = $barword->settings){
		foreach($words as $key => $bad){
			$i++;
			if ($i%2 == 0){$bg = ' class="enabled"';}
			else {$bg = ' class="disabled"';}

		if ($bad){$buffer .= '<tr'.$bg.'><td>'.$bad.'<td><a href="'.$PHP_SELF.'?mod=options&amp;action=spam&amp;subaction=remove&amp;id='.$key.'">[Remove]</a>';}
		}
	}

	$buffer .= '</table></table>';

	if ($_POST['add_badword']){
		$barword -> settings[] = strtolower($_POST['add_badword']);
		$barword -> save();

		$buffer = 'The word was added!<br><br><a href="'.$PHP_SELF.'?mod=options&amp;action=spam">Back to the list</a>';
	}

	if ($_GET['subaction'] == 'remove'){
		unset($barword -> settings[$_GET['id']]);
		$barword -> save();

		$buffer = 'The word was removed from the list!<br><br><a href="'.$PHP_SELF.'?mod=options&amp;action=spam">Back to the list</a>';
	}

	echo $buffer;

	echofooter();
}
?>