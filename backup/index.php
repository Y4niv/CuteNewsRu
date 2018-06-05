<?php
include_once '../head.php';
$echo = cute_lang('addons/backup');

if (!$is_logged_in or ($is_logged_in and $member['level'] > 2)){
	exit($echo['error']);
}

if ($_GET['action'] != 'data' and $_GET['action'] != 'database'){
?>

<li><a href="<?=$PHP_SELF; ?>?action=data"><?=$echo['downloadData']; ?></a>

<?
	if ($config_database != 'txtsql'){
?>

<li><a href="<?=$PHP_SELF; ?>?action=database"><?=$echo['downloadDatabase']; ?></a>

<?
	}

	echo $echo['info'];
}

if ($_GET['action'] == 'data'){
	include 'zipbackup.php';
	$zipfile = new zipfile();

	function listdir($dir = '../data/', $to = 'data/'){
		global $zipfile;
	    $fdir = opendir($dir);
	    while($file = readdir($fdir)){
	        if ($file != '.' and $file != '..'){
	            if (is_file($dir.$file)){
	                $zipfile->add_file(file_get_contents($dir.$file), $to.$file);
	            }

	            if (is_dir($dir.$file)){
	            	listdir($dir.$file.'/', $to.$file.'/');
	            }
	        }
	    }
	}

	listdir();

	header('Content-type: application/octet-stream');
	header('Content-disposition: attachment; filename=backup_'.date('d.m.Y').'.zip');
	echo $zipfile->file();
}

if ($_GET['action'] == 'database' or $_POST or $_GET['filename']){
	define('DB_HOST', $config_dbserver);
	define('DB_USER', $config_dbuser);
	define('DB_PASS', $config_dbpassword);
	define('DB_NAME', $config_dbname);
	define('ADMIN_EMAIL', ($config_admin_mail ? $config_admin_mail : 'none'));

	if ($_GET['filename']){
		include 'dosql.php';
	} else {
		include 'backupDB.php';
	}
}
?>
