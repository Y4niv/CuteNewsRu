<?php
define('cache', true);
define('global_cache', false);
define('chmod', 0777);
define('cookie', true);
define('session', false);
define('check_referer', false);

#-------------------------------------------------------------------------------

error_reporting(E_ALL & ~E_NOTICE);


if (global_cache and !$_POST){
	$global_cache_file = dirname(__FILE__).'/cache/__'.md5(($_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : 'main')).'.tmp';

	if ($_POST['action'] == 'addcomment'){
		@unlink($global_cache_file);
	}

	if (!file_exists($global_cache_file)){
		function cute_global_cache($buffer){
		global $global_cache_file;

			$buffer = preg_replace('/\[if-logged\](.*?)\[\/if-logged\]/si', '', $buffer);
			$buffer = str_replace('[not-logged]', '', $buffer);
			$buffer = str_replace('[/not-logged]', '', $buffer);

			$fp = @fopen($global_cache_file, 'wb+');
			@fwrite($fp, $buffer);
			@fclose($fp);

		return $buffer;
		}

		ob_start('cute_global_cache');
	} else {
		exit(file_get_contents($global_cache_file));
	}
}

@extract($_SESSION, EXTR_SKIP);
@extract($_COOKIE, EXTR_SKIP);
@extract($_GET, EXTR_SKIP);
@extract($_ENV, EXTR_SKIP);
@extract($_POST, EXTR_SKIP);

$vars = array(
'skip',
'page',
'action',
'id',
'ucat',
'category',
'number',
'template',
'static',
'year',
'month',
'day',
'title',
'sort',
'user',
'author',
'time'
);

$default = array(
'cutepath'		   => dirname(__FILE__),
'phpversion'	   => @phpversion(),
'HTTP_REFERER'	   => $_SERVER['HTTP_REFERER'],
'DOCUMENT_ROOT'	   => $_SERVER['DOCUMENT_ROOT'],
'PHP_SELF'		   => htmlentities($_SERVER['PHP_SELF']),
'is_logged_in'	   => false,
'cookie_logged'	   => false,
'session_logged'   => false,
'a7f89abdcf9324b3' => '',
'cache_uniq'	   => 0
);

foreach ($default as $k => $v){
	unset($_GET[$k], $_POST[$k], $_SESSION[$k], $_COOKIE[$k], $_ENV[$k], $_CUTE[$k]);
	$$k = $v;
}

include_once $cutepath.'/data/config.php';

$config_database = ($config_database ? $config_database : 'txtsql');
$config_lang	 = ($config_lang ? $config_lang : 'en');

include_once $cutepath.'/inc/lang/'.$config_lang.'/functions.php';
include_once $cutepath.'/inc/db/'.$config_database.'.inc.php';

foreach ($vars as $k => $v){
	$$k = @htmlspecialchars($v);
}

foreach ($sql->select(array('table' => 'categories', 'orderby' => array('id', 'ASC'))) as $row){
	$cat_name[$row['id']]	  = $row['name'];
	$cat_icon[$row['id']]	  = $row['icon'];
	$cat_url[$row['id']]	  = $row['url'];
	$cat_template[$row['id']] = $row['template'];
	$cat_parent[$row['id']]	  = $row['parent'];
}

foreach ($sql->select(array('table' => 'users')) as $row){
	if (!$row['hide_mail'] and $row['mail']){
		$user_name[$row['username']] = '<a href="mailto:'.str_replace('@', ' at ', str_replace('.', ' dot ', $row['mail'])).'">'.($row['name'] ? $row['name'] : $row['username']).'</a>';
	} else {
		$user_name[$row['username']] = ($row['name'] ? $row['name'] : $row['username']);
	}

	$user_id[$row['username']] = $row['id'];
	$user_avatar[$row['username']] = ($row['avatar'] ? '<img src="'.$config_path_userpic_upload.'/'.$row['username'].'.'.$row['avatar'].'" alt="" border="0">' : '');
	$members[] = $row;
}

include_once $cutepath.'/inc/functions.inc.php';
include_once $cutepath.'/inc/plugins.inc.php';

$echo = cute_lang();

if (session){
	@session_start();
	@header('Cache-control: private');
}

if (substr($HTTP_REFERER, -1) == '/'){
	$HTTP_REFERER .= $PHP_SELF;
}

if (cookie){
	if ($username){
		if ($_COOKIE['md5_password']){
			$cmd5_password = $_COOKIE['md5_password'];
		} else {
			$cmd5_password = md5x($password);
		}

		if (check_login($username, $cmd5_password)){
			$cookie_logged = true;

			@setcookie('lastusername', $username, time() + 1012324305, '/');
			@setcookie('username', $username, time() + 3600 * 24 * 365, '/');
			@setcookie('md5_password', $cmd5_password, time() + 3600 * 24 * 365, '/');

		} else {
			$result = '<font color="red">'.$echo['loginError'].'</font>';
			$cookie_logged = false;
	   }
	}
}

if (session){
	if ($action == 'dologin'){
		$md5_password = md5x($password);

		if (check_login($username, $md5_password)){
			$session_logged = true;

			@session_register('username');
			@session_register('md5_password');
			@session_register('ip');
			@session_register('login_referer');

			$_SESSION['username']	   = $username;
			$_SESSION['md5_password']  = $md5_password;
			$_SESSION['ip']			   = $_SERVER['REMOTE_ADDR'];
			$_SESSION['login_referer'] = $HTTP_REFERER;

		} else {
			$result = '<font color="red">'.$echo['loginError'].'</font>';
			$session_logged = false;
		}
	} elseif ($_SESSION['username']){
		if (check_login($_SESSION['username'], $_SESSION['md5_password'])){
			if ($_SESSION['ip'] != $ip){
				$session_logged = false;
				$result = $echo['sessionError'];
			} else {
				$session_logged = true;
			}
		} else {
			$result = '<font color="red">'.$echo['loginError'].'</font>';
			$session_logged = false;
		}
	}

	if (!$username){
		$username = $_SESSION['username'];
	}
}

if ($session_logged or $cookie_logged){
	$is_logged_in = true;

	if ($action == 'dologin'){
		$sql->update(array(
		'table'	 => 'users',
		'where'	 => array("username = $username"),
		'values' => array('last_visit' => (time() + $config_date_adjust * 60))
		));
	}
}

LoadActivePlugins();
run_actions('head');

@extract($_CUTE, EXTR_SKIP);
?>