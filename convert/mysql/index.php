<?php
include '../../head.php';

$echo = cute_lang('convert');
$step = $_GET['step'];
$step = ($step ? $step : 1);

if ($step == 2){
	$config_database   = 'mysql';
	$config_dbname     = $_POST['dbname'];
	$config_dbuser     = $_POST['dbuser'];
	$config_dbpassword = $_POST['dbpassword'];
	$config_dbprefix   = $_POST['dbprefix'];
	$config_dbserver   = $_POST['dbserver'];
}

if ($step == 3){
	header('Location: '.$config_http_script_dir, true);
	exit;
}

if ($step != 1){
	include_once $cutepath.'/inc/db/txtsql.class.php';
	$txtSQl = new txtSQL($cutepath.'/data/db');
	$txtSQl->connect('root', '');
	$txtSQl->selectdb('base');

    include_once $cutepath.'/inc/db/mysql.class.php';
	$MySQL = new MySQL();
	$MySQL->connect($config_dbuser, $config_dbpassword, $config_dbserver);
	$MySQL->selectdb($config_dbname, $config_dbprefix);
}

function insert($table){
global $txtSQl, $MySQL, $config_dbprefix;

    if ($select = $txtSQl->select(array('table' => $table))){
	    foreach ($select as $row){
	        foreach ($row as $k => $v){
	            $values[$k] = $v;
	        }

	        $MySQL->insert(array(
	        'table'  => $table,
	        'values' => $values
	        ));
		}
	}
}
?>

<table width="200" border="0" cellspacing="0" cellpadding="0">
<form action="<?=$_SERVER['PHP_SELF']; ?>?step=<?=($step + 1); ?>" method="post">

<?
if ($step == 1){
?>

 <tr>
  <td colspan="2"><br /><br /><b><?=$echo['database']; ?></b>:
 <tr>
  <td><?=$echo['dbUser']; ?>
  <td><input name="dbuser" type="text" value="">
 <tr>
  <td><?=$echo['dbPassword']; ?>
  <td><input name="dbpassword" type="text" value="">
 <tr>
  <td><?=$echo['dbServer']; ?>
  <td><input name="dbserver" type="text" value="localhost">
 <tr>
  <td><?=$echo['dbName']; ?>
  <td><input name="dbname" type="text" value="">
 <tr>
  <td><?=$echo['dbPrefix']; ?>
  <td><input name="dbprefix" type="text" value="cute_">

<?
} elseif ($step == 2){

	include('./db.php');

	$config = file_read($cutepath.'/data/config.php', $config);
	$config = str_replace('?>', '', $config);
	$config = $config.
'
$config_database = "mysql";

$config_dbname = "'.$config_dbname.'";

$config_dbuser = "'.$config_dbuser.'";

$config_dbpassword = "'.$config_dbpassword.'";

$config_dbprefix = "'.$config_dbprefix.'";

$config_dbserver = "'.$config_dbserver.'";
?>';

	file_write($cutepath.'/data/config.php', $config);
} 
?>

 <tr>
  <td colspan="2"><br /><br /><input type="submit" value="<?=sprintf($echo['next'], (($step + 1) == 2 ? $echo['end'] : $step + 1)); ?>">
</form>
</table>