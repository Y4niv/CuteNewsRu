<?php
/*
Plugin Name:	Crossposting to LJ
Plugin URI:		http://cutenews.ru
Description:	Crossposting in <a href="http://www.livejournal.com">LJ</a> and tags.<ul style="margin-top: 3px;margin-bottom: 3px;margin-left: 30px;"><li><img src="./skins/images/user.gif" align="absmiddle">user <code>&lt;lj user="user"&gt;</code><li><img src="./skins/images/comm.gif" align="absmiddle">community <code>&lt;lj comm="community"&gt;</code><li><img src="./skins/images/synd.gif" align="absmiddle">syndication <code>&lt;lj synd="syndication"&gt;</code><li>Cut <code>&lt;lj-cut&gt;&lt;/lj-cut&gt;</code> or <code>&lt;lj-cut text="cut"&gt;&lt;/lj-cut&gt;</code></ul>
Application:	CuteNews
Author:			&#1051;&#1105;&#1093;&#1072; zloy &#1080; &#1082;&#1088;&#1072;&#1089;&#1080;&#1074;&#1099;&#1081;
Author URI:		http://lexa.cutenews.ru
*/

add_action('new-save-entry', 'CuteNews2LJ');
add_action('edit-save-entry', 'CuteNews2LJ');

add_filter('cutenews-options', 'CuteNews2LJ_AddToOptions');
add_action('plugin-options','CuteNews2LJ_CheckAdminOptions');

add_action('new-advanced-options', 'CuteNews2LJ_AddEdit', 999);
add_action('edit-advanced-options', 'CuteNews2LJ_AddEdit', 999);

add_filter('news-entry','CuteNews2LJ_tags');
add_filter('news-comment','CuteNews2LJ_tags');

function CuteNews2LJ_AddEdit(){
global $mod, $id;

	$echo	 = cute_lang('plugins/lj-crosspost');
	$xfields = new XfieldsData();

	if ($xfields->fetch($id, 'itemid') or $mod == 'addnews'){
		return '<fieldset><legend>'.$echo['lj'].'</legend><label for="cn2lj"><input type="checkbox" id="cn2lj" name="cn2lj" value="on" checked="checked">&nbsp;'.($mod == 'addnews' ? $echo['add'] : $echo['edit']).'</label>';
	}
}

function CuteNews2LJ(){

	if ($_POST['cn2lj'] == 'on'){
		CuteNews2LJ_Start();
	}
}

function CuteNews2LJ_Start(){
global $title, $short_story, $url, $full_story, $added_time, $mod, $id, $category;

	#---------------------------------------------------------------------------

	// Edit your username and password
	$username = 'lexazloy';
	$password = 'password';

	#---------------------------------------------------------------------------

	include rootpath.'/data/config.php';
	include rootpath.'/inc/xmlrpc.inc.php';

	$methodName = (($mod == 'addnews') ? 'postevent' : 'editevent');
	$xfields	= new XfieldsData();
	$itemid		= $xfields->fetch($id, 'itemid');
	$tpl		= new PluginSettings('CN2LJ');

	if (!$tpl->settings['title']){
	   $tpl->settings['title'] = '{title}';
	   $tpl->save();
	}

	if (!$tpl->settings['story']){
	   $tpl->settings['body'] = '{story}{nl}{nl}<p style="text-align: right;"><a href="{link}" style="color: #666;font-size: 9px;" title="&laquo;{title}&raquo;">{hometitle}</a>';
	   $tpl->save();
	}

	$find		= array('{hometitle}', '{homelink}', '{title}', '{link}', '{story}');
	$replace	= array($config_home_title, $config_http_home_url, $title, cute_get_link(array('id' => $id, 'date' => $added_time, 'title' => $title, 'category' => $category, 'url' => $url)), $short_story);
	$array		= array(XMLRPC_prepare(array(
				  'username'	=> $username,
				  'hpassword'	=> md5($password),
				  'subject'		=> str_replace($find, $replace, replace_news('admin', $tpl->settings['title'])),
				  'event'		=> str_replace($find, $replace, replace_news('admin', $tpl->settings['story'])),
				  'lineendings' => 'unix',
				  'ver'			=> 1,
				  'year'		=> date('Y', $added_time),
				  'mon'			=> date('m', $added_time),
				  'day'			=> date('d', $added_time),
				  'hour'		=> date('H', $added_time),
				  'min'			=> date('i', $added_time),
				  'itemid'		=> $itemid
				  )));

	$lj = XMLRPC_request('www.livejournal.com', '/interface/xmlrpc', 'LJ.XMLRPC.'.$methodName, $array, 'PHP XMLRPC 1.0');

	if ($methodName == 'postevent'){
		$xfields->set(($lj[1]['itemid'] * 256 + $lj[1]['anum']), $id, 'itemid');
		$xfields->save();
	}
}

function CuteNews2LJ_AddToOptions($options){
global $PHP_SELF;

	$echo	   = cute_lang('plugins/lj-crosspost');
	$options[] = array(
		'name'	 => $echo['header'],
		'url'	 => $PHP_SELF.'?mod=options&amp;action=lj',
		'access' => 1
	);

return $options;
}

function CuteNews2LJ_CheckAdminOptions(){

	if ($_GET['action'] == 'lj'){
		CuteNews2LJ_AdminOptions();
	}
}

function CuteNews2LJ_AdminOptions(){

	$echo = cute_lang('plugins/lj-crosspost');
	$tpl  = new PluginSettings('CN2LJ');

	echoheader('options', $echo['header']);

	if (!$tpl->settings['title']){
	   $tpl->settings['title'] = '{title}';
	   $tpl->save();
	}

	if (!$tpl->settings['story']){
	   $tpl->settings['story'] = '{story}{nl}{nl}<p style="text-align: right;"><a href="{link}" style="color: #666;font-size: 9px;" title="&laquo;{title}&raquo;">{hometitle}</a>';
	   $tpl->save();
	}

	if ($_POST['title'] or $_POST['story']){
		$tpl->settings['title'] = replace_news('add', $_POST['title']);
		$tpl->settings['story'] = replace_news('add', $_POST['story']);
		$tpl->save();
?>

<?=$echo['saved']; ?>
<p><a href="javascript:history.go(-1)"><?=$echo['back']; ?></a>

<?
		echofooter();
		exit;
	}
?>

<?=$echo['info']; ?>
<br /><br />
<form method="post" action="?mod=options&amp;action=lj">
<p><?=$echo['subj']; ?><br /><input type="text" name="title" value="<?=replace_news('admin', $tpl->settings['title']); ?>" style="width: 250px;">
<p><?=$echo['story']; ?><br /><textarea name="story" rows="15" cols="74"><?=replace_news('admin', $tpl->settings['story']); ?></textarea>
<p><input type="submit" name="submit" value=" <?=$echo['save']; ?> ">
</form>

<?
	echofooter();
}

function CuteNews2LJ_tags(){
global $output, $id, $config_http_script_dir, $allow_full_story, $xfields;

	if (!is_object($xfields)){
		$xfields = new XfieldsData();
	}

	$itemid = $xfields->fetch($id, 'itemid');
	$output = str_replace('{lj-itemid}', $itemid, $output);
	$output = preg_replace('/\[lj-link( user=(\\\"|"|\'{0,1})(.*?)(\\2))?\](.*?)\[\/lj-link\]/i', ($itemid ? '<a href="http://www.livejournal.com/users/\\3/'.$itemid.'.html">\\5</a>' : ''), $output);
	$output = preg_replace('/<lj (.*?)=(\\\"|"|\'{0,1})(.*?)(\\2)>/i', '<a href="http://www.livejournal.com/userinfo.bml?user=\\3"><img height="17" src="'.$config_http_script_dir.'/skins/images/\\1.gif" width="17" align="absmiddle" border="0" alt="[info]"></a><a href="http://www.livejournal.com/users/\\3/">\\3</a>', $output);

	preg_match_all('/(<(lj-cut)( text=(\\\"|"|\'{0,1})(.*?)(\\4))?>)(.*?)(<\/\\2>)/i', $output, $matches);

	if ($allow_full_story){
		for ($i = 0; $i < count($matches[7]); $i++){
			$output = str_replace($matches[7][$i], '<a name="cutid'.($i+1).'"></a>'.$matches[7][$i], $output);
		}
	} else {
		for ($i = 0; $i < count($matches[7]); $i++){
			$output = str_replace($matches[7][$i], '<span class="cutid">(&nbsp;[link=#cutid'.($i+1).']'.($matches[5][$i] ? $matches[5][$i] : 'Read More...').'[/link]&nbsp;)</span>', $output);
		}
	}

	$output = preg_replace('/<lj-cut(.*?)>(.*?)<\/lj-cut>/i', '\\2', $output);

return $output;
}
?>