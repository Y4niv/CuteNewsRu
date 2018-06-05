<?php
error_reporting(E_ALL & ~E_NOTICE);
$cutepath = str_replace('\\', '/', substr(dirname(__FILE__), 0, -8));

include_once $cutepath.'/inc/functions.inc.php';
include_once $cutepath.'/inc/plugins.inc.php';
@include_once $cutepath.'/skins/default.skin.php';

@chmoddir($cutepath.'/data', 0777);
@chmoddir($cutepath.'/cache', 0777);
@chmod($cutepath.'/data', 0755);

$handle = opendir($cutepath.'/inc/lang');
while ($file = readdir($handle)){
	if ($file != '.' and $file != '..' and is_dir($cutepath.'/inc/lang/'.$file)){
		$sys_con_lang_arr[$file] = file_read($cutepath.'/inc/lang/'.$file.'/langname');
	}
}

$handle = opendir($cutepath.'/inc/db');
while ($file = readdir($handle)){
	if (substr($file, -3) != 'php' and is_file($cutepath.'/inc/db/'.$file)){
		$sys_con_database_arr[$file] = file_read($cutepath.'/inc/db/'.$file);
	}
}

function check_writable($dir){
global $cutepath;

	$handle = opendir($cutepath.'/'.$dir);
	while (false !== ($file = readdir($handle))){
		if ($file != '.' and $file != '..' and $file != '.htaccess' and substr($file, -3) != 'gif'){
			$path = $dir.'/'.$file;

			if (is_file($path)){
				echo '<font color="'.(is_writable($path) ? 'green' : 'red').'">'.$path.'</font><br />';
			} else {
				echo '<font color="'.(is_writable($path) ? 'green' : 'red').'">'.$path.'/</font><br />';
				check_writable($path);
			}
		}
	}
}

$config_database   = strtolower($_POST['database']);
$config_lang	   = $_POST['lang'];
$config_dbname	   = $_POST['dbname'];
$config_dbuser	   = $_POST['dbuser'];
$config_dbpassword = $_POST['dbpassword'];
$config_dbprefix   = $_POST['dbprefix'];
$config_dbserver   = $_POST['dbserver'];
$echo			   = cute_lang('install');
$step			   = $_GET['step'];
$step			   = ($step ? $step : 1);
$url			   = preg_replace('/\/index.php$/i', '', reset($url = explode('?', $_SERVER['HTTP_REFERER'])));

echoheader('options', $echo['header']);
?>

<table width="200" border="0" cellspacing="0" cellpadding="0">
<form action="<?=$_SERVER['PHP_SELF']; ?>?step=<?=($step + 1); ?>" method="post">
<input name="lang" type="hidden" value="<?=$config_lang; ?>">
<input name="database" type="hidden" value="<?=$config_database; ?>">

<?
if ($step == 1){
?>

 <tr>
  <td><?=$echo['lang']; ?>
  <td><?=makeDropDown($sys_con_lang_arr, 'lang', ''); ?>

 <tr>
  <td><?=$echo['database']; ?>
  <td><?=makeDropDown($sys_con_database_arr, 'database', 'txtsql'); ?>

<?
} elseif ($step == 2){
	echo $echo['chmod'];
	echo '<font color="'.(is_writable('cache') ? 'green' : 'red').'">cache/</font><br />';
	echo check_writable('data');
} elseif ($step == 3){
	if ($config_database == 'txtsql'){
		$disabled = ' disabled';
	}
?>

 <tr>
  <td><?=$echo['login']; ?>
  <td><input name="login" type="text" value="">
 <tr>
  <td><?=$echo['password']; ?>
  <td><input name="password" type="text" value="">
 <tr>
  <td colspan="2"><br /><br /><b><?=$echo['database']; ?></b>:
 <tr>
  <td><?=$echo['dbUser']; ?>
  <td><input name="dbuser" type="text" value=""<?=$disabled; ?>>
 <tr>
  <td><?=$echo['dbPassword']; ?>
  <td><input name="dbpassword" type="text" value=""<?=$disabled; ?>>
 <tr>
  <td><?=$echo['dbServer']; ?>
  <td><input name="dbserver" type="text" value="localhost"<?=$disabled; ?>>
 <tr>
  <td><?=$echo['dbName']; ?>
  <td><input name="dbname" type="text" value=""<?=$disabled; ?>>
 <tr>
  <td><?=$echo['dbPrefix']; ?>
  <td><input name="dbprefix" type="text" value="cute_"<?=$disabled; ?>>

<?
} elseif ($step == 4){
	include $cutepath.'/inc/db/'.$config_database.'.inc.php';
	include $cutepath.'/install/db.php';
	include $cutepath.'/install/config.php';
	file_write($cutepath.'/data/config.php', $config);
	$sql->insert(array(
	'table'	 => 'users',
	'values' => array(
				'date'	   => time(),
				'level'	   => 1,
				'username' => $_POST['login'],
				'password' => md5x($_POST['password'])
				)
	));
}
?>

 <tr>
  <td colspan="2"><br /><br /><input type="submit" value="<?=sprintf($echo['next'], (($step + 1) == 5 ? $echo['end'] : ($step + 1))); ?>">
</form>
</table>

<?
echofooter();
exit;
?>