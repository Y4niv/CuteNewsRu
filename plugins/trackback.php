<?php
/*
Plugin Name:	TrackBack
Plugin URI:		http://cutenews.ru
Description:	If you do not know that such use <a href="http://www.google.com/search?hl=en&q=TrackBack">Google</a>.
Version:		1.0
Application:	CuteNews
Author:			&#1051;&#1105;&#1093;&#1072; zloy &#1080; &#1082;&#1088;&#1072;&#1089;&#1080;&#1074;&#1099;&#1081;
Author URI:		http://lexa.cutenews.ru
*/

add_action('new-advanced-options', 'trackback_AddEdit');
add_action('edit-advanced-options', 'trackback_AddEdit');

function trackback_AddEdit(){
global $id;

	$xfields = new XfieldsData();
	$echo	 = cute_lang('plugins/trackback');
	$return	 = '<fieldset><legend>'.$echo['send'].'</legend><textarea name="ping" title="'.$echo['help'].'">'.$xfields->fetch($id, 'ping').'</textarea></fieldset>'.($xfields->fetch($id, 'pinged') ? '<fieldset><legend>'.$echo['pinged'].'</legend><textarea disabled>'.replace_news('admin', $xfields->fetch($id, 'pinged')).'</textarea></fieldset>' : '');

return $return;
}

add_action('new-save-entry', 'trackback_send');
add_action('edit-save-entry', 'trackback_send');

function trackback_send(){
global $id, $added_time, $member_db, $title, $category, $url, $short_story, $ping, $PHP_SELF;

	include rootpath.'/data/config.php';

	$echo	  = cute_lang('plugins/trackback');
	$sendfrom = parse_url($config_http_script_dir);

	foreach (explode("\r\n", $ping) as $sendto){
		request($sendfrom['host'], $sendto, 'blog_name='.$config_home_title.'&url='.cute_get_link(array('id' => $id, 'date' => $added_time, 'author' => $member['username'], 'title' => $title, 'category' => $category, 'url' => $url)).'&title='.$title.'&excerpt='.replace_news('show', $short_story).'&charset='.$echo['charset']);
	}

	$xfields = new XfieldsData();
	$pinged	 = $xfields->fetch($id, 'pinged');
	$xfields->set(replace_news('add', ($ping ? $pinged."\r\n".$ping : $pinged)), $id, 'pinged');
	$xfields->deletefield($id, 'ping');
	$xfields->save();
}

add_filter('cutenews-options', 'trackback_AddToOptions');
add_action('plugin-options','trackback_CheckAdminOptions');

function trackback_AddToOptions($options) {
global $PHP_SELF;

	include plugin_xfields_file;
	foreach ($array as $arr){
		if (count($arr['trackback'])){
			$count .= count($arr['trackback']);
		}
	}

	$echo	   = cute_lang('plugins/trackback');
	$options[] = array(
		'name'	 => $echo['header'].' ('.($count ? $count : 0).')',
		'url'	 => $PHP_SELF.'?mod=options&amp;action=trackback',
		'access' => 1
	);

return $options;
}

function trackback_CheckAdminOptions(){

	if ($_GET['action'] == 'trackback'){
		trackback_AdminOptions();
	}
}

function trackback_AdminOptions(){
global $sql, $PHP_SELF;

	$echo	 = cute_lang('plugins/trackback');
	$xfields = new XFieldsData();

	if ($_POST['select_trackbacks']){
		foreach ($_POST['select_trackbacks'] as $time => $id){
			if ($_POST['add']){
				$trackback = $xfields->fetch($id, 'trackback');
				$trackback = $trackback[$time];

				$sql->insert(array(
				'table'	 => 'comments',
				'values' => array(
							'post_id' => $id,
							'date'	  => $time,
							'author'  => $trackback['blog_name'],
							'mail'	  => $trackback['url'],
							'ip'	  => $trackback['host'],
							'comment' => ($trackback['title'] ? '[b]'.$trackback['title'].'[/b]<br />' : '').$trackback['excerpt']
							)
				));

				$sql->update(array(
				'table'	 => 'news',
				'where'	 => array("id = $id"),
				'values' => array('comments' => count($sql->select(array('table' => 'comments', 'where' => array("post_id = $id")))))
				));
			}

			$xfields->deletevalue($id, 'trackback', $time);
			$xfields->save();
		}
?>

<script type="text/javascript">self.location.href="<?=$_SERVER['REQUEST_URI']; ?>";</script>

<?
	}

	echoheader('options', $echo['header']);
?>

<form name="trackbacks" action="<?=$PHP_SELF; ?>?mod=options&amp;action=trackback" method="post">

<?
	include rootpath.'/data/xfields-data.php';
	foreach ($array as $k => $v){
		if ($v['trackback']){
			foreach ($v['trackback'] as $time => $info){
?>

<h3><a href="<?=$info['url']; ?>" title="<?=$info['host']; ?>"><?=$info['blog_name']; ?></a></h3>
<div align="justify">
<small><?=langdate('d M Y H:i', $time); ?> /</small> <b><?=$info['title']; ?></b>
<br />
<?=$info['excerpt']; ?>
<input name="select_trackbacks[<?=$time; ?>]" type="checkbox" value="<?=$k; ?>">
</div>

<?
			}
		}
	}

	if ($info){
?>

<p>
<input type="submit" value="  <?=$echo['add']; ?>  " name="add">
<input type="submit" value="  <?=$echo['delete']; ?>  " name="delete">
</p>
</form>

<?
	} else {
?>

<p><?=$echo['empty']; ?></p>

<?
	}

	echofooter();
}

#-------------------------------------------------------------------------------

function request($site, $location, $send, $user_agent = ''){

	list($site, $port) = explode(':', $site);

	$fp = fsockopen($site, (is_numeric($port) ? $port : 80));
	$fo = "POST $location HTTP/1.0\r\n".
		  "Host: $site\r\n".
		  ($user_agent ? "User-Agent: $user_agent\r\n" : '').
		  "Content-Type: application/x-www-form-urlencoded; charset=utf-8\r\n".
		  "Content-Length: ".strlen($send)."\r\n\r\n".
		  $send;
	fputs($fp, $fo);
}
?>