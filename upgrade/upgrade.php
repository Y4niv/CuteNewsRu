<?php
$sql->strict(false);
$db		 = ($config_database == 'txtsql' ? 'base': $config_dbname);
$echo	 = cute_lang('upgrade');
$config	 = file_read($cutepath.'/data/config.php', $config);
$config	 = str_replace('?>', '', $config);
$config .= '$config_version_id = "'.$config_cutenews_built.'";';

/* to 2.2 */
$sql->altertable(array(
'table'	 => 'users',
'action' => 'insert',
'name'	 => 'categories',
'values' => array('type' => 'string')
));
$sql->altertable(array(
'table'	 => 'news',
'action' => 'insert',
'name'	 => 'hidden',
'values' => array('type' => 'bool', 'default' => 0)
));

/* to 2.3 */
$sql->altertable(array(
'table'	 => 'category',
'action' => 'insert',
'name'	 => 'template',
'values' => array('type' => 'string')
));
$sql->altertable(array(
'table'	 => 'category',
'action' => 'rename table',
'name'	 => 'categories'
));

/* to 2.4 */
$urls = parse_ini_file($cutepath.'/data/urls.ini', true);

if ($urls['config']['rufus'] and $urls['config']['rufus'] == 'yes'){
	$config .= "\r\n\r\n";
	$config .= '$config_rufus = "yes";';
}

$handle = opendir($cutepath.'/data');
while ($file = readdir($handle)){
	if (substr($file, -3) == 'tpl'){
		$contents = str_replace('{link=plain/', '{link=home/', file_read($cutepath.'/data/'.$file));
		@file_write($cutepath.'/data/'.$file, $contents);
	}

	if ($file == 'urls.ini'){
		$contents = str_replace('[plain]', '[home]', file_read($cutepath.'/data/'.$file));
		file_write($cutepath.'/data/'.$file, $contents);
	}
}

/* to 2.5 */
umask(0);
@mkdir($cutepath.'/data/tpl', 0777);
@chmod($cutepath.'/data/tpl', 0777);

$handle = opendir($cutepath.'/data');
while ($file = readdir($handle)){
	if (substr($file, -3) == 'tpl'){
		if (@copy($cutepath.'/data/'.$file, $cutepath.'/data/tpl/'.$file)){
			@unlink($cutepath.'/data/'.$file);
		}
	}
}

$sql->altertable(array(
'table'	 => 'categories',
'action' => 'modify',
'name'	 => 'id',
'values' => array('default' => 0)
));

$sql->altertable(array(
'table'	 => 'story',
'action' => 'modify',
'name'	 => 'post_id',
'values' => array('default' => 0)
));

$sql->altertable(array(
'table'	 => 'flood',
'action' => 'modify',
'name'	 => 'post_id',
'values' => array('default' => 0)
));

$sql->altertable(array(
'table'	 => 'categories',
'action' => 'addkey',
'name'	 => 'id'
));

$sql->altertable(array(
'table'	 => 'story',
'action' => 'addkey',
'name'	 => 'post_id'
));

$sql->altertable(array(
'table'	 => 'flood',
'action' => 'addkey',
'name'	 => 'post_id'
));

file_write($cutepath.'/data/config.php', $config);
header('Location: '.$PHP_SELF);
exit;
?>