<?php
include_once dirname(__FILE__).'/txtsql.class.php';
$sql = new txtSQL($cutepath.'/data/db');
$sql->connect('root', '');

if ($sql->db_exists('base')){
	$sql->selectdb('base');
}
?>