<?php

$that = '-'; // чем замен€ть пробел в ”–Ћах

#-------------------------------------------------------------------------------

include '../../head.php';
$echo = cute_lang('convert');

if (!$is_logged_in or $is_logged_in and $member['level'] != 1){
	exit($echo['error']);
}

$abort = @ignore_user_abort(1);
$path  = realpath('.');

if (!ini_get('safe_mode')){
	@set_time_limit(0);
}

function check_writable(){
global $path;

	$handle = opendir($path);
	while ($file = readdir($handle)){
		if (substr($file, -3) == 'tmp'){
			if (is_file($file)){
				echo '<li><font color="'.(is_writable($file) ? 'green' : 'red').'">'.$file.'</font><br />';
			}
		}
	}
}

function not_null($file){
global $path;

	if (file_exists($path.'/'.$file.'.tmp')){
		if (filesize($path.'/'.$file.'.tmp')){
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function write_and_go($file, $text = 'ok'){
global $PHP_SELF, $path;

	@chmod($path.'/'.$file, 0777);
	@file_write($path.'/'.$file.'.tmp', $text);
	@header('Location: '.$PHP_SELF);
}

if (not_null('news')){
	foreach (file($path.'/news.tmp') as $fo){
		$fo_arr				 = explode('|', $fo);
		$post_id[$fo_arr[0]] = $fo_arr[1];
	}
}

if ($fp = file($path.'/data/counter.txt')){
	foreach ($fp as $fo){
		$fo_arr				 = explode('|', $fo);
		$counter[$fo_arr[0]] = $fo_arr[1];
	}
}

if ($_GET['action'] == 'users'){
	$fp = file($path.'/data/users.db.php');
	foreach ($fp as $fo){
		$fo_arr = explode('|', $fo);

		if (!$sql->update(array(
		'table'	 => 'users',
		'where'	 => array("username = $fo_arr[2]"),
		'values' => array(
					'date'		   => $fo_arr[0],
					'level'		   => $fo_arr[1],
					'name'		   => $fo_arr[4],
					'mail'		   => $fo_arr[5],
					'publications' => $fo_arr[6],
					'hide_mail'	   => $fo_arr[7],
					'avatar'	   => $fo_arr[8],
					'last_visit'   => $fo_arr[9],
					'homepage'	   => $fo_arr[10],
					'icq'		   => $fo_arr[11],
					'location'	   => $fo_arr[12],
					'about'		   => $fo_arr[13],
					'lj_username'  => $fo_arr[14],
					'lj_password'  => ''
					)
		)) and $fo_arr[2]){
			$sql->insert(array(
			'table'	 => 'users',
			'values' => array(
						'date'		   => $fo_arr[0],
						'level'		   => $fo_arr[1],
						'username'	   => $fo_arr[2],
						'password'	   => md5($fo_arr[3]),
						'name'		   => $fo_arr[4],
						'mail'		   => $fo_arr[5],
						'publications' => $fo_arr[6],
						'hide_mail'	   => $fo_arr[7],
						'avatar'	   => $fo_arr[8],
						'last_visit'   => $fo_arr[9],
						'homepage'	   => $fo_arr[10],
						'icq'		   => $fo_arr[11],
						'location'	   => $fo_arr[12],
						'about'		   => $fo_arr[13],
						'lj_username'  => $fo_arr[14],
						'lj_password'  => ''
						)
			));
		}
	}

	write_and_go('users');
}

if ($_GET['action'] == 'categories'){
	$fp = file($path.'/data/category.db.php');
	foreach ($fp as $fo){
		$fo_arr = explode('|', $fo);

		$sql->insert(array(
		'table'	 => 'categories',
		'values' => array(
					'id'	 => $fo_arr[0],
					'name'	 => $fo_arr[1],
					'icon'	 => $fo_arr[2],
					'url'	 => ($fo_arr[3] ? $fo_arr[3] : totranslit($fo_arr[1], $that)),
					'parent' => $fo_arr[4]
					)
		));
	}

	write_and_go('categories');
}

if ($_GET['action'] == 'news'){
	$news_arr[] = $path.'/data/news.txt';
	$fdir = opendir($path.'/data/archives');
	while ($file = readdir($fdir)){
		$file_arr = explode('.', $file);

		if (is_numeric($file_arr[0]) and $file_arr[1] == 'news'){
			$news_arr[] = $path.'/data/archives/'.$file;
		}
	}

	foreach ($news_arr as $file){
		foreach (file($file) as $fo){
			$all_news_arr[] = $fo;
		}
	}

	sort($all_news_arr);

	for ($i = 0; $i < sizeof($all_news_arr); $i++){
		$fo_arr = explode('|', $all_news_arr[$i]);

		$sql->insert(array(
		'table'	 => 'news',
		'values' => array(
					'date'	   => $fo_arr[0],
					'author'   => $fo_arr[1],
					'title'	   => $fo_arr[2],
					'short'	   => strlen($fo_arr[3]),
					'full'	   => ($fo_arr[4] ? strlen($fo_arr[4]) : 0),
					'avatar'   => $fo_arr[5],
					'views'	   => $counter[$fo_arr[0]],
					'category' => $fo_arr[6],
					'url'	   => ($fo_arr[7] ? namespace($fo_arr[7]) : namespace(totranslit($fo_arr[2], $that)))
					)
		));

		if ($config_database == 'txtsql'){
			$last_insert_id = ($sql->last_insert_id('news', '', 'id') + $i);
		} else {
			$last_insert_id = $sql->last_insert_id('news', '', 'id');
		}

		$write .= $fo_arr[0].'|'.$last_insert_id."\r\n";

		$sql->insert(array(
		'table'	 => 'story',
		'values' => array(
					'post_id' => $last_insert_id,
					'short'	  => $fo_arr[3],
					'full'	  => $fo_arr[4]
					)
		));
	}

	write_and_go('news', $write);
}

if ($_GET['action'] == 'comments'){
	$comm_arr[] = $path.'/data/comments.txt';
	$fdir = opendir($path.'/data/archives');
	while ($file = readdir($fdir)){
		$file_arr = explode('.', $file);

		if (is_numeric($file_arr[0]) and $file_arr[1] == 'comments'){
			$comm_arr[] = $path.'/data/archives/'.$file;
		}
	}

	foreach ($comm_arr as $file){
		foreach (file($file) as $fo){
			$all_comm_arr[] = $fo;
		}
	}

	sort($all_comm_arr);

	foreach ($all_comm_arr as $comment_line){
		$comment_arr_1 = explode('|>|', $comment_line);
		$comment_arr_2 = explode('||', $comment_arr_1[1]);

		foreach ($comment_arr_2 as $fo){
			$fo_arr = explode('|', $fo);

			if ($fo_arr[2] and $post_id[$comment_arr_1[0]]){
				$sql->insert(array(
				'table'	 => 'comments',
				'values' => array(
							'date'	  => $fo_arr[0],
							'author'  => $fo_arr[1],
							'mail'	  => $fo_arr[2],
							'ip'	  => $fo_arr[3],
							'comment' => $fo_arr[4],
							'reply'	  => $fo_arr[5],
							'post_id' => $post_id[$comment_arr_1[0]],
							)
				));

				$sql->update(array(
				'table'	 => 'news',
				'where'	 => array('id = '.$post_id[$comment_arr_1[0]]),
				'values' => array('comments' => count($comment_arr_2) - 1)
				));
			}
		}
	}

	write_and_go('comments');
}

if ($_GET['action'] == 'xfields'){
	function xz($id){
	global $post_id, $repl1;

		$repl1 = str_replace("\r\n", '', $post_id[$id]);

	return $repl1;
	}

	$file1 = file($path.'/data/plugins/xfields-data.php');
	$file2 = file($path.'/data/xfieldsdata.txt');
	$file3 = file_read($path.'/data/xfields.txt');

	foreach ($file1 as $fo1){
		$replace1_tmp = preg_replace('/([0-9]{10})/ie', "xz('\\1')", $fo1);

		if ($repl1){
			$replace1 .= $replace1_tmp;
		}
	}

	foreach ($file2 as $fo2){
		$fo_arr2 = explode('|>|', $fo2);

		if ($replace2_tmp = str_replace("\r\n", '', $post_id[$fo_arr2[0]])){
			$replace2 .= $replace2_tmp.'|>|'.$fo_arr2[1];
		}
	}

	file_write($cutepath.'/data/xfields-data.php', "<?php\r\n\$array = array (\r\n".$replace1);
	file_write($cutepath.'/data/xfields-data.txt', $replace2);
	file_write($cutepath.'/data/xfields.txt', $file3);

	write_and_go('xfields');
}
?>

<?=$echo['chmod']; ?>
<?=check_writable(); ?>
<?=$echo['choose']; ?>

<?
if (!not_null('users')){
?>

<li><a href="<?=$PHP_SELF; ?>?action=users"><?=$echo['users']; ?>*</a>

<?
}

if (!not_null('categories')){
?>

<li><a href="<?=$PHP_SELF; ?>?action=categories"><?=$echo['categories']; ?></a>

<?
}

if (!not_null('news')){
?>

<li><a href="<?=$PHP_SELF; ?>?action=news"><?=$echo['news']; ?></a>

<?
}

if (not_null('news')){
	if (!not_null('comments')){
?>

<li><a href="<?=$PHP_SELF; ?>?action=comments"><?=$echo['comments']; ?></a>

<?
	}

	if (!not_null('xfields')){
?>

<li><a href="<?=$PHP_SELF; ?>?action=xfields"><?=$echo['xfields']; ?></a>

<?
	}
} else {
?>

<li><?=$echo['comments']; ?>**
<li><?=$echo['xfields']; ?>**

<?
}
?>

<p>* <?=$echo['helpUsers']; ?>
<p>** <?=$echo['helpCommentsAndXfields']; ?>