<?php
include '../../head.php';

$echo = cute_lang('convert');
$step = $_GET['step'];
$step = ($step ? $step : 1);

if ($step == 2){
	$config_database   = 'txtsql';
	$config_dbname     = '';
	$config_dbuser     = '';
	$config_dbpassword = '';
	$config_dbprefix   = '';
	$config_dbserver   = '';
}

if ($step == 4){
	header('Location: '.$config_http_script_dir, true);
	exit;
}

if ($step != 1){
	include_once $cutepath.'/inc/db/txtsql.class.php';
	$txtSQl = new txtSQL($cutepath.'/data/db');
	
	$txtSQl->connect('root', '');
	$dbname = 'base';
	if (!$txtSQl->db_exists($dbname)){
			$txtSQl->createdb(array('db' => $dbname));
	}
	$txtSQl->selectdb('base');

}

function insert($table){
global $txtSQl, $sql, $config_dbprefix;

    if ($select = $sql->select(array('table' => $table))){
	    foreach ($select as $row){
	        foreach ($row as $k => $v){
	            $values[$k] = $v;
	        }

	        $txtSQl->insert(array(
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
  <td colspan="2">This will convert your MySQL database to txtSQL.

<?
} elseif ($step == 2){
	include 'db.php';
}

elseif ($step == 3){
	$config = file_read($cutepath.'/data/config.php', $config);
	$config = str_replace('?>', '', $config);
	$config = $config.
'
$config_database = "txtsql";

?>';

	file_write($cutepath.'/data/config.php', $config);
}
?>

 <tr>
  <td colspan="2"><br /><br /><input type="submit" value="<?=sprintf($echo['next'], (($step + 1) == 4 ? $echo['end'] : $step + 1)); ?>">
</form>
</table>