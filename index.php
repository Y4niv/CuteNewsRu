<?php

$PHP_SELF				 = 'index.php';
$config_cutenews_version = 'CuteNews.RU';
$config_cutenews_built	 = '2.5.4';

#-------------------------------------------------------------------------------

if (!filesize('data/config.php')){
	include 'install/install.php';
}

include_once 'head.php';

if ($config_cutenews_built > $config_version_id and $is_logged_in and $member['level'] == 1){
	include 'upgrade/upgrade.php';
}

@chmoddir($cutepath.'/data', chmod);
@chmoddir($cutepath.'/cache', chmod);
@chmod($cutepath.'/data', 0755);

$timer = new microTimer;
$timer->start();

if ($action == 'logout'){
	setcookie('md5_password', '', time() - 3600 * 24 * 365, '/');
	setcookie('username', '', time() - 3600 * 24 * 365, '/');
	setcookie('login_referer', '');
	@session_destroy();
	@session_unset();
	@setcookie(session_name(), '');
?>

<!-- Javascript redirect -->
<script type="text/javascript">self.location.href="<?=$_SERVER['PHP_SELF']; ?>";</script>

<?
}

if (isset($config_skin) and $config_skin and file_exists($cutepath.'/skins/'.$config_skin.'.skin.php')){
	require_once $cutepath.'/skins/'.$config_skin.'.skin.php';
} else {
	$using_safe_skin = true;
	require_once $cutepath.'/skins/default.skin.php';
}

b64dck();

// If User is Not Logged In, Display The Login Page
if (!$is_logged_in){
	if (session){
		@session_destroy();
		@session_unset();
	}

	setcookie('username', '', time() - 3600 * 24 * 365, '/');
	setcookie('password', '');
	setcookie('md5_password', '', time() - 3600 * 24 * 365, '/');
	setcookie('login_referer', '');
	echoheader('user', $echo['header']);
?>

<table border="0" cellspacing="0" cellpadding="1">
 <form name="login" action="<?=$PHP_SELF; ?>" method="post" onsubmit="return process_form(this)">
  <tr>
   <td width="80"><?=$echo['username']; ?>
   <td><input tabindex="1" type="text" name="username" value="<?=$lastusername; ?>" style="width:134">
  <tr>
   <td><?=$echo['password']; ?>
   <td><input type="password" name="password" style="width:134">
  <tr>
   <td>
   <td><input accesskey="s" type="submit" style="width:134;" value="<?=$echo['login']; ?>">
  <tr>
   <td align="center" colspan="3"><?=$result; ?></td>
  </tr>
 <input type="hidden" name="action" value="dologin">
 </form>
</table>

<?
   echofooter();
} elseif ($is_logged_in){
	if (check_referer){
		$self = $_SERVER['SCRIPT_NAME'];

		if (!$self){
			$self = $_SERVER['REDIRECT_URL'];
		}

		if (!$self){
			$self = $PHP_SELF;
		}

		if (!eregi($self, $HTTP_REFERER) and $HTTP_REFERER){
			echo sprintf($echo['badReferer'], $PHP_SELF);
			exit;
		}
	}

// ********************************************************************************
// Include System Module
// ********************************************************************************
	if ($_SERVER['QUERY_STRING'] == 'debug'){
		debug();
	}

	if ($member['level'] > 3 and $mod != 'personal' and $mod != 'editcomments' and $action != 'dosavepersonal'){
		header('Location: '.$PHP_SELF.'?mod=personal');
		exit;
	}

	$system_modules = array(
					  //name of mod	  //access
					  'addnews'		 => 'user',
					  'editnews'	 => 'user',
					  'main'		 => 'user',
					  'options'		 => 'user',
					  'personal'	 => 'user',
					  'images'		 => 'user',
					  'editusers'	 => 'admin',
					  'plugins'		 => 'admin',
					  'syscon'		 => 'admin',
					  'templates'	 => 'admin',
					  'editcomments' => 'user',
					  'tools'		 => 'admin',
					  'ipban'		 => 'admin',
					  'about'		 => 'user',
					  'categories'	 => 'admin',
					  'help'		 => 'user',
					  'snr'			 => 'admin',
					  'debug'		 => 'admin',
					  'preview'		 => 'user'
					  );


	if (!$mod){
		require $cutepath.'/inc/mod/main.mdu';
	} elseif ($system_modules[$mod]){
		if ($system_modules[$mod] == 'user'){
			require $cutepath.'/inc/mod/'.$mod.'.mdu';
		} elseif ($system_modules[$mod] == 'admin' and $member['level'] == 1){
			require $cutepath.'/inc/mod/'.$mod.'.mdu';
		} elseif ($system_modules[$mod] == 'admin' and $member['level'] != 1){
			msg('error', $echo['error'], $echo['moduleAccess']);
		} elseif ($system_modules[$mod] == 'any'){
			require $cutepath.'/inc/mod/'.$mod.'.mdu';
		}
	} else {
		msg('error', $echo['error'], $echo['wrongModule']);
	}
}
?>

<!-- Страница сгенерирована за <?=$timer->stop(); ?> сек. -->