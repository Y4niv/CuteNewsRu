<?php
include_once dirname(__FILE__).'/mysql.class.php';
$sql = new MySQL();
$sql->connect($config_dbuser, $config_dbpassword, $config_dbserver);
$sql->selectdb($config_dbname, $config_dbprefix);
?>