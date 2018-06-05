<?php
$sql->strict(false);

if ($config_database == 'txtsql'){
	$dbname = 'base';
	if (!$sql->db_exists($dbname)){
		$sql->createdb(array('db' => $dbname));
	}

	$sql->selectdb($dbname);
} else {
	$dbname = $config_dbname;
	$sql->selectdb($dbname, $config_dbprefix);
}

if (!$sql->table_exists('users', $dbname)){
	$sql->createtable(array(
	'table'	  => 'users',
	'columns' => array(
				 'date'			=> array('type' => 'int'),
				 'level'		=> array('type' => 'int', 'default' => 4),
				 'username'		=> array('type' => 'string'),
				 'password'		=> array('type' => 'string'),
				 'name'			=> array('type' => 'string'),
				 'mail'			=> array('type' => 'string'),
				 'publications' => array('type' => 'int', 'default' => 0),
				 'hide_mail'	=> array('type' => 'bool'),
				 'avatar'		=> array('type' => 'string'),
				 'last_visit'	=> array('type' => 'int'),
				 'homepage'		=> array('type' => 'string'),
				 'icq'			=> array('type' => 'int'),
				 'location'		=> array('type' => 'string'),
				 'about'		=> array('type' => 'text'),
				 'lj_username'	=> array('type' => 'string'),
				 'lj_password'	=> array('type' => 'string'),
				 'id'			=> array(
								   'type'			=> 'int',
								   'auto_increment' => 1,
								   'primary'		=> 1
								   ),
				 'categories'	=> array('type' => 'string')
				 )
	));
}

if ($sql->table_exists('users', $dbname)){
	echo '<br><font color="green">'.sprintf($echo['table'], 'users').'</font>';
}

if (!$sql->table_exists('categories', $dbname)){
	$sql->createtable(array(
	'table'	  => 'categories',
	'columns' => array(
				 'id'		=> array('type' => 'int', 'primary' => 1),
				 'name'		=> array('type' => 'string'),
				 'icon'		=> array('type' => 'string'),
				 'url'		=> array('type' => 'string'),
				 'parent'	=> array('type' => 'int'),
				 'template' => array('type' => 'string')
				 )
	));
}

if ($sql->table_exists('categories', $dbname)){
	echo '<br><font color="green">'.sprintf($echo['table'], 'categories').'</font>';
}

if (!$sql->table_exists('comments', $dbname)){
	$sql->createtable(array(
	'table'	  => 'comments',
	'columns' => array(
				 'date'	   => array('type' => 'int'),
				 'author'  => array('type' => 'string'),
				 'mail'	   => array('type' => 'string'),
				 'ip'	   => array('type' => 'string'),
				 'comment' => array('type' => 'text'),
				 'reply'   => array('type' => 'text'),
				 'post_id' => array('type' => 'int'),
				 'id'	   => array(
							  'type'		   => 'int',
							  'auto_increment' => 1,
							  'primary'		   => 1
							  )
				 )
	));
}

if ($sql->table_exists('comments', $dbname)){
	echo '<br><font color="green">'.sprintf($echo['table'], 'comments').'</font>';
}

if (!$sql->table_exists('news', $dbname)){
	$sql->createtable(array(
	'table'	  => 'news',
	'columns' => array(
				 'date'		=> array('type' => 'int'),
				 'author'	=> array('type' => 'string'),
				 'title'	=> array('type' => 'string'),
				 'short'	=> array('type' => 'int'),
				 'full'		=> array('type' => 'int', 'default' => 0),
				 'avatar'	=> array('type' => 'string'),
				 'category' => array('type' => 'string'),
				 'url'		=> array('type' => 'string'),
				 'id'		=> array(
							   'type'			=> 'int',
							   'auto_increment' => 1,
							   'primary'		=> 1
							   ),
				 'views'	=> array('type' => 'int', 'default' => 0),
				 'comments' => array('type' => 'int', 'default' => 0),
				 'hidden'	=> array('type' => 'bool', 'default' => 0)
				 )
	));
}

if ($sql->table_exists('news', $dbname)){
	echo '<br><font color="green">'.sprintf($echo['table'], 'news').'</font>';
}

if (!$sql->table_exists('ipban', $dbname)){
	$sql->createtable(array(
	'table'	  => 'ipban',
	'columns' => array(
				 'ip'	 => array('type' => 'string'),
				 'count' => array('type' => 'int', 'default' => 0)
				 )
	));
}

if ($sql->table_exists('ipban', $dbname)){
	echo '<br><font color="green">'.sprintf($echo['table'], 'ipban').'</font>';
}

if (!$sql->table_exists('flood', $dbname)){
	$sql->createtable(array(
	'table'	  => 'flood',
	'columns' => array(
				 'date'	   => array('type' => 'int'),
				 'ip'	   => array('type' => 'string'),
				 'post_id' => array('type' => 'int', 'primary' => 1)
				 )
	));
}

if ($sql->table_exists('flood', $dbname)){
	echo '<br><font color="green">'.sprintf($echo['table'], 'flood').'</font>';
}

if (!$sql->table_exists('story', $dbname)){
	$sql->createtable(array(
	'table'	  => 'story',
	'columns' => array(
				 'post_id' => array('type' => 'int', 'primary' => 1),
				 'short'   => array('type' => 'text'),
				 'full'	   => array('type' => 'text')
				 )
	));
}

if ($sql->table_exists('story', $dbname)){
	echo '<br><font color="green">'.sprintf($echo['table'], 'story').'</font>';
}
?>