<?php

$all_tables = $sql->showtables(array());


foreach($all_tables as $table){
	
	$table_details[$table] = $sql->describe(array('table' => $table));
}



foreach($table_details as $table => $columns){

	foreach($columns as $field => $value){
		$primary = '';
		if($field == 'primary' and $value != ''){
			$primary = $value;
		}
		if($field != 'primary'){
			$output[$table][$field]['type'] = $value['type'];
			if(is_numeric($value['default'])){
				$output[$table][$field]['default'] = $value['default'];
			}
			if($value['auto_increment'] == 1){
				$output[$table][$field]['auto_increment'] = $value['auto_increment'];
			}
		}
		
		elseif($primary){
			$output[$table][$primary]['primary'] = 1;
			$output[$table][$primary]['type'] = $value['type'];
			if(is_numeric($value['default'])){
				$output[$table][$primary]['default'] = $value['default'];
			}
			if($value['auto_increment'] == 1){
				$output[$table][$primary]['auto_increment'] = $value['auto_increment'];
			}
		}
	}
}

foreach($output as $table => $columns){
	$dbname = $config_dbname;

	if (!$MySQL->table_exists($table, $dbname)){
		$MySQL->createtable(array(
		'table'   => $table,
		'columns' => $columns
		));
	}
	
	insert($table);
}

?>