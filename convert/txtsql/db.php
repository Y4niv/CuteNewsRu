<?php
include '../../data/config.php';

mysql_connect($config_dbserver, $config_dbuser, $config_dbpassword);
mysql_select_db($config_dbname);

$all_tables = mysql_list_tables($config_dbname);

while ($row = mysql_fetch_row($all_tables)) {
	if(preg_match_all("#$config_dbprefix#", $row[0], $matches))
    $my_tables[] = $row[0];
}

function replace_it($text){
$find = array("varchar(255)", "tinyint(1)", "int(11)", "auto_increment", "PRI");
$replace = array("string", "bool", "int", "1", "1");
return str_replace($find, $replace, $text);
}

foreach($my_tables as $single_table){

$result = mysql_query("SHOW COLUMNS FROM ".$single_table);

	while ($test = mysql_fetch_row($result)){

		$output[str_replace($config_dbprefix, "", $single_table)][$test[0]] = array('type' => replace_it($test[1]), 'default' => $test[4], 'primary' => replace_it($test[3]), 'auto_increment' => replace_it($test[5]));
		
	}
}



mysql_free_result($result);
mysql_close();

foreach($output as $table => $value){

		if (!$txtSQl->table_exists($table, $dbname)){
			$txtSQl->createtable(array(
			'table'	  => $table,
			'columns' => $value
			));
		}
		
			insert($table);

}


?>