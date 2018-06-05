<?php
define('ROOTPATH', ($cutepath ? $cutepath : '.'), true);
define('ACTIVE_PLUGINS_FILE', ROOTPATH.'/data/plugins.php', true);
define('PLUGINS_DIRECTORY', ROOTPATH.'/plugins', true);
define('PLUGINS_DATA_DIRECTORY', ROOTPATH.'/data', true);
define('PLUGIN_SETTINGS_FILE', PLUGINS_DATA_DIRECTORY.'/settings.php', true);
define('PLUGIN_XFIELDS_FILE', PLUGINS_DATA_DIRECTORY.'/xfields-data.php', true);
define('PLUGIN_FRAMEWORK_VERSION', '1.1.5', true);
define('PLUGIN_DEFAULT_PRIORITY', 50, true);


function LoadActivePlugins() {
	foreach (active_plugins() as $plugin_filename => $active) {
		$path = PLUGINS_DIRECTORY.'/'.$plugin_filename;
		if (is_file($path))
			include($path);
		else
			disable_plugin($plugin_filename);
	}
}

function plugin_enabled($plugin_filename) {
	$plugins = active_plugins();
	if ($plugins[$plugin_filename])
		return true;
	else
		return false;
}

function enable_plugin($plugin_filename) {
	$plugins = active_plugins();
	$plugins[$plugin_filename] = true;
	SaveArray($plugins, ACTIVE_PLUGINS_FILE);

}

function disable_plugin($plugin_filename) {
	$plugins = active_plugins();
	unset($plugins[$plugin_filename]);
	SaveArray($plugins, ACTIVE_PLUGINS_FILE);
}



/* List Plugins */

function available_plugins() {
	$ffl = FileFolderList(PLUGINS_DIRECTORY,1);
	$plugins = $ffl[file];
	if (!empty($plugins))
		foreach ($plugins as $pluginfile) {
			$plugin_data = GetContents($pluginfile);
			preg_match("{Plugin Name:(.*)}i", $plugin_data, $plugin[name]);
			preg_match("{Plugin URI:(.*)}i", $plugin_data, $plugin[uri]);
			preg_match("{Description:(.*)}i", $plugin_data, $plugin[description]);
			preg_match("{Author:(.*)}i", $plugin_data, $plugin[author]);
			preg_match("{Author URI:(.*)}i", $plugin_data, $plugin[author_uri]);
			preg_match("{Version:(.*)}i", $plugin_data, $plugin[version]);
			preg_match("{Application:(.*)}i", $plugin_data, $plugin[application]);
			preg_match("{Required Framework:(.*)}i", $plugin_data, $plugin[framework]);

			$required_version = trim($plugin[framework][1]);
			$application = trim($plugin[application][1]);

			// Skip plugins that need a better framework
			if ($required_version && version_compare(PLUGIN_FRAMEWORK_VERSION, $required_version, '<'))
				$compatible = false;
			else
				$compatible = true;

			// Skip plugins designed for other systems
			if ($application and strtolower($application) != 'cutenews' and strtolower($application) != 'cutenews.ru')
				continue;

			$available_plugins[] = array(
				name		=> trim($plugin[name][1]),
				uri			=> trim($plugin[uri][1]),
				description	=> trim($plugin[description][1]),
				author		=> trim($plugin[author][1]),
				author_uri	=> trim($plugin[author_uri][1]),
				version		=> trim($plugin[version][1]),
				application	=> trim($plugin[application][1]),
				file		=> basename($pluginfile),
				framework	=> $required_version,
				compatible	=> $compatible,
			);
		}
	else
		$available_plugins = array();
	return $available_plugins;
}

function active_plugins() {
	if (!is_file(ACTIVE_PLUGINS_FILE))
		return array();

	$active_plugins = LoadArray(ACTIVE_PLUGINS_FILE);

	return $active_plugins;
}




/* Actions And Filters */

function add_action($hook, $functionname, $priority = PLUGIN_DEFAULT_PRIORITY) {
	global $actions;
	$actions[$hook][] = array(
		'name' => $functionname,
		'priority' => $priority,
	);
}

function run_actions($hookname) {
	global $actions;
	$todo = $actions[$hookname];
	if (!$todo)
		return false;
	usort($todo, 'SortByActionPriority');
	foreach ($todo as $action)
		$buffer .= $action['name']($hookname);
	return $buffer;
}

function add_filter($hook, $functionname, $priority = PLUGIN_DEFAULT_PRIORITY) {
	global $filters;
	$filters[$hook][] = array(
		'name' => $functionname,
		'priority' => $priority,
	);
}

function run_filters($hookname, $tofilter) {
	global $filters;
	$todo = $filters[$hookname];
	if ($todo) {
		usort($todo, 'SortByActionPriority');
		foreach ($todo as $filter)
			$tofilter = $filter['name']($tofilter, $hookname);
	}
	return $tofilter;
}

function SortByActionPriority($a, $b) {
	return $a[priority] > $b[priority] ? 1 : -1;
}


/* File Functions */

function FileFolderList($path, $depth = 0, $current = '', $level=0) {
	if ($level==0 && !@file_exists($path))
		return false;
	if (is_dir($path)) {
		$handle = @opendir($path);
		if ($depth == 0 || $level < $depth)
			while($filename = @readdir($handle))
				if ($filename != '.' && $filename != '..')
					$current = @FileFolderList($path.'/'.$filename, $depth, $current, $level+1);
		@closedir($handle);
		$current[folder][] = $path.'/'.$filename;
	} else
		if (is_file($path))
			$current[file][] = $path;
	return $current;
}

function LoadArray($pathandfilename) {
	if (is_file($pathandfilename)) {
		@include($pathandfilename);
		return $array;
	}
	return array();
}

function WriteContents($contents,$filename) {
	if (file_exists($filename))
		if (!is_writable($filename))
			if (!chmod($filename, chmod))
				 return false;
	if (!$fp = @fopen($filename, 'wb+'))
		return false;
	if (@fwrite($fp, $contents) === false)
		return false;
	if (!@fclose($fp))
		return false;
	return true;
}

function GetContents($filename) {
	$file = @fopen($filename, 'rb');
	if ($file) {
		while (!feof($file)) $data .= fread($file, 1024);
		fclose($file);
	} else {
		return false;
	}
	return $data;
}

function SaveArray($array,$filename) {
	$contents = '<?php
$array = '. var_export($array,1) .';
?>';
	return WriteContents($contents, $filename);
}

/* Data Handling Classes */

class PluginSettings {
	function PluginSettings($plugin_name) {
		$this->name = $plugin_name;
		$this->all_settings = LoadArray(PLUGIN_SETTINGS_FILE);
		$this->settings = $this->all_settings[$plugin_name];
	}

	function save() {
		$this->all_settings[$this->name] = $this->settings;
		return SaveArray($this->all_settings, PLUGIN_SETTINGS_FILE);
	}

	function delete() {
		unset($this->settings);
		return $this->save();
	}
}

class XFieldsData {
	function XFieldsData() {
		$this->file = PLUGIN_XFIELDS_FILE;
		$this->data = LoadArray(PLUGIN_XFIELDS_FILE);
	}

	function fetch($news_id, $field_name) {
		return $this->data[$news_id][$field_name];
	}

	function set($value, $news_id, $field_name) {
		$this->data[$news_id][$field_name] = $value;
	}

	function increment($news_id, $field_name) {
		return $this->data[$news_id][$field_name]++;
	}

	function decrement($news_id, $field_name) {
		return $this->data[$news_id][$field_name]--;
	}

	function delete($news_id) {
		unset($this->data[$news_id]);
	}

	function deletefield($news_id, $field_name) {
		unset($this->data[$news_id][$field_name]);
	}

	function deletevalue($news_id, $field_name, $value) {
		unset($this->data[$news_id][$field_name][$value]);
	}

	function save() {
		return SaveArray($this->data, $this->file);
	}
}

/* XFields self-cleaning plugin */

add_action('deleted-single-entry', 'clean_single_xfields');
add_action('deleted-multiple-entries', 'clean_multiple_xfields');

function clean_single_xfields($hook){
global $row, $id;

	$xfields = new XfieldsData();
	$xfields->delete(($id ? $id : $row['id']));
	$xfields->save();
}

function clean_multiple_xfields($hook) {
global $selected_news;

	$xfields = new XfieldsData();

	foreach ($selected_news as $news_id){
		$xfields->delete($news_id);
	}

	$xfields->save();
}

function multicats($that){
global $sql, $id, $member, $config_user_categories;

	foreach ($sql->select(array('table' => 'users', 'where' => array("id = $member[id]"))) as $row){
	    if (!in_array($that, explode(',', $row['categories'])) and $member['level'] == 3 and $config_user_categories == 'yes'){
	    	return 'disabled';
	    }
	}

    if ($id){
	    foreach ($sql->select(array('table' => 'news', 'where' => array("id = $id"))) as $row){
	        if (in_array($that, explode(',', $row['category']))){
	            return 'checked';
	        }
	    }
	}
}

add_action('new-advanced-options', 'multicats_AddEdit', 1);
add_action('edit-advanced-options', 'multicats_AddEdit', 1);

function multicats_AddEdit(){
global $id, $mod;

	$echo = cute_lang($mod);

	if ($category = category_get_tree('&nbsp;', '<label for="cat{id}"><input type="checkbox" [php]multicats({id})[/php] name="cat[{id}]" id="cat{id}")">&nbsp;{name}</label><br />')){
		return '<fieldset><legend>'.$echo['category'].'</legend>'.$category.'</fieldset>';
	}
}

add_action('new-save-entry', 'multicats_Save', 1);
add_action('edit-save-entry', 'multicats_Save', 1);

function multicats_Save(){
global $cat, $category;

    if ($cat){
		foreach ($cat as $k => $v){
			$category_tmp[] = $k;
		}

		$category = join(',', $category_tmp);
	}
}

add_action('head', 'cache_remove');

function cache_remove(){
global $id, $row, $mod, $is_logged_in, $action, $member;

    if ($is_logged_in and $member['level'] == 1 and $action == 'clearcache'){
    	cache_remover();
    } elseif (($is_logged_in and $mod and $_POST) or (global_cache and $_POST['action'] == 'addcomment')){
		cache_remover((($id or $row['id']) ? ($id ? $id : $row['id']) : ''));
	}
}

add_action('head', 'rufus');

function rufus(){
global $is_logged_in, $mod, $config_http_home_url, $config_rufus;

	if ($config_rufus != 'yes' and !$mod){
		$urls = parse_ini_file(rootpath.'/data/urls.ini', true);
	    foreach ($urls as $url_k => $url_v){
	        foreach ($url_v as $k => $v){
	            @preg_match_all('/'.@str_replace('/', '\/', htaccess_rules_replace($v)).'/i', $_SERVER['REQUEST_URI'], $query);
	            for ($i = 0; $i < count($query); $i++){
	                if ($query[$i][0]){
	                    if ($clear = preg_replace('/(.*?)=\$([0-9]+)/i', '', str_replace('$'.$i, $query[$i][0], str_replace('?', '', htaccess_rules_format($v))))){
	                        $str[] = $clear;
	                    }
	                }
	            }
	        }
	    }

	    if ($str){
	        $str = preg_replace('/([\&]+)/i', '&', join('&', array_reverse($str)));
	        parse_str($str, $_CUTE);

	        foreach ($_CUTE as $k => $v){
	            $GLOBALS[$k] = $_GET[$k] = @htmlspecialchars($v);
	        }
	    }
	}
}

add_action('new-advanced-options', 'rufus_AddEdit', 1);
add_action('edit-advanced-options', 'rufus_AddEdit', 1);

function rufus_AddEdit(){
global $row;

	$echo = cute_lang('rufus');

return '<fieldset id="url"><legend>'.$echo['url'].'</legend><input type="text" size="42" name="url" value="'.$row['url'].'"></fieldset>';
}

add_action('head', 'make_htaccess');

function make_htaccess(){
global $mod, $PHP_SELF, $config_http_script_dir, $config_http_home_url, $config_rufus;

	$settings         = cute_parse_url($config_http_home_url);
	$config           = cute_parse_url($config_http_script_dir);
	$echo             = cute_lang('rufus');
	$types            = parse_ini_file(rootpath.'/data/urls.ini', true);
	$settings['path'] = ($settings['path'] ? '/'.$settings['path'].'/' : '/');
	$config['path']   = ($config['path'] ? '/'.$config['path'].'/' : '/');
	$uhtaccess        = new	PluginSettings('uhtaccess');

	if ($mod and $_POST and $settings['file'] and $config_rufus == 'yes'){
	    $htaccess .= 'DirectoryIndex '.$settings['file']."\r\n\r\n";
	    $htaccess .= '# [user htaccess] '."\r\n".$uhtaccess->settings."\r\n\r\n";
	    $htaccess .= '<IfModule mod_rewrite.c>'."\r\n";
	    $htaccess .= 'RewriteEngine On'."\r\n";
	    $htaccess .= '#Options +FollowSymlinks'."\r\n";
	    $htaccess .= '#RewriteBase '.$settings['path']."\r\n\r\n";

	    foreach ($types as $type_k => $type_v){
	        foreach ($type_v as $k => $v){
	            $htaccess .= '# ['.$type_k.'] '.$k."\r\n";
	            $htaccess .= 'RewriteRule ^'.(($type_k == 'home' or !is_dir($settings['abs'].'/'.$type_k) or !is_file($settings['abs'].'/'.$type_k)) ? '' : $type_k.'/').htaccess_rules_replace($v).'(/?)+$ '.htaccess_rules_format($v, ($type_k != 'home' ? ((is_dir($settings['abs'].'/'.$type_k) or is_file($settings['abs'].'/'.$type_k)) ? $type_k.'/' : $config['path'].$type_k.'.php') : '')).' [QSA,L]'."\r\n";
	        }
	    }

	    $htaccess .= '</IfModule>';

		if (!is_writable($settings['abs'].'/.htaccess')){
			@chmod($settings['abs'].'/.htaccess', chmod);
		}

		file_write($settings['abs'].'/.htaccess', $htaccess);
	}
}

add_filter('cutenews-options', 'rufus_AddToOptions');
add_action('plugin-options', 'rufus_CheckAdminOptions');

function rufus_AddToOptions($options){
global $PHP_SELF;

	$echo      = cute_lang('rufus');
	$options[] = array(
			     'name'	  => $echo['header'],
			     'url'	  => $PHP_SELF.'?mod=options&amp;action=rufus',
			     'access' => 1
	);

return $options;
}

function rufus_CheckAdminOptions(){

	if ($_GET['action'] == 'rufus'){
		rufus_AdminOptions();
	}
}

function rufus_AdminOptions(){
global $PHP_SELF, $config_http_script_dir, $config_http_home_url, $config_rufus;

	if ($_POST){
		header('Location: '.$PHP_SELF.'?mod=options&action=rufus');
	}

	$settings         = cute_parse_url($config_http_home_url);
	$config           = cute_parse_url($config_http_script_dir);
	$echo             = cute_lang('rufus');
	$types            = parse_ini_file(rootpath.'/data/urls.ini', true);
	$settings['path'] = ($settings['path'] ? '/'.$settings['path'].'/' : '/');
	$config['path']   = ($config['path'] ? '/'.$config['path'].'/' : '/');
	$uhtaccess        = new	PluginSettings('uhtaccess');

	echoheader('user', $echo['header']);

	if (!$settings['file']){
		echo $echo['error'];
		return;
	}

	if (ini_get('safe_mode') and $config_rufus == 'yes'){
		echo sprintf($echo['safe_mode'], $settings['abs']);
	}

	$htaccess .= 'DirectoryIndex '.$settings['file']."\r\n\r\n";
	$htaccess .= '# [user htaccess] '."\r\n".$uhtaccess->settings."\r\n\r\n";
	$htaccess .= '<IfModule mod_rewrite.c>'."\r\n";
	$htaccess .= 'RewriteEngine On'."\r\n";
	$htaccess .= '#Options +FollowSymlinks'."\r\n";
	$htaccess .= '#RewriteBase '.$settings['path']."\r\n\r\n";

    foreach ($types as $type_k => $type_v){
        foreach ($type_v as $k => $v){
            $htaccess .= '# ['.$type_k.'] '.$k."\r\n";
            $htaccess .= 'RewriteRule ^'.(($type_k == 'home' or !is_dir($settings['abs'].'/'.$type_k) or !is_file($settings['abs'].'/'.$type_k)) ? '' : $type_k.'/').htaccess_rules_replace($v).'(/?)+$ '.htaccess_rules_format($v, ($type_k != 'home' ? ((is_dir($settings['abs'].'/'.$type_k) or is_file($settings['abs'].'/'.$type_k)) ? $type_k.'/' : $config['path'].$type_k.'.php') : '')).' [QSA,L]'."\r\n";
        }
    }

    $htaccess .= '</IfModule>';

	echo sprintf($echo['about'], $echo['save'], $echo['make']);
?>

<form action="<?=$PHP_SELF; ?>?mod=options&amp;action=rufus" method="post">
<h3>.htaccess:</h3>
<textarea name="uhtaccess" rows="5" cols="20"<?=($config_rufus != 'yes' ? ' disabled>'.$echo['rufus'] : '>'.$uhtaccess->settings); ?></textarea>
<h3>urls.ini:</h3>
<textarea name="urls_content" rows="5" cols="20"><?=file_read(rootpath.'/data/urls.ini'); ?></textarea>
<br /><br />
<input type="submit" name="urls" value="  <?=$echo['save']; ?>  "> <input type="submit" name="htaccess" value="   <?=($config_rufus != 'yes' ? $echo['rufus'].'   " disabled' : $echo['make'].'   "'); ?>>
</form>

<?
	if ($_POST['urls']){
		if (!is_writable(rootpath.'/data/urls.ini')){
			@chmod(rootpath.'/data/urls.ini', chmod);
		}

        $uhtaccess->settings = $_POST['uhtaccess'];
        $uhtaccess->save();
		file_write(rootpath.'/data/urls.ini', replace_news('admin', $_POST['urls_content']));
	}

	if ($_POST['htaccess']){
		if (!is_writable($settings['abs'].'/.htaccess')){
			@chmod($settings['abs'].'/.htaccess', chmod);
		}

        $uhtaccess->settings = $_POST['uhtaccess'];
        $uhtaccess->save();
		file_write($settings['abs'].'/.htaccess', $htaccess);
	}

	echofooter();
}

function htaccess_rules_replace($output){
global $cat_url, $cat_parent, $config_rufus;

    if ($_POST['catid'] and $_POST['name']){
		$cat_url[$_POST['catid']]    = ($_POST['url'] ? $_POST['url'] : totranslit($_POST['name']));
		$cat_parent[$_POST['catid']] = $_POST['parent'];
	}

	if ($cat_url and $config_rufus == 'yes'){
	    foreach ($cat_url as $k => $v){
	    	$category[] = $cat_url[$k];

	        if (!$cat_parent[$k]){
	            $categories[] = $cat_url[$k];
	        } else {
	            $categories[] = category_get_link($k);
	        }
	    }

	    if ($categories){
	        $categories = join('|', $categories);
	        $categories = '(none|'.$categories.')';
	    }

	    if ($category){
	        $category = join('|', $category);
	        $category = '(none|'.$category.')';
	    }
	} else {
		$category   = '([_0-9a-z-]+)';
		$categories = '([/_0-9a-z-]+)';
	}

    $output = preg_replace('/{(.*?):(.*?)}/i', '{\\1}', $output);
	$output = strtr($output, array(
	          '{id}'          => '([0-9]+)',
	          '{year}'        => '([0-9]{4})',
	          '{month}'       => '([0-9]{2})',
	          '{Month}'       => '([0-9a-z]{2,3})',
	          '{day}'         => '([0-9]{2})',
	          '{time}'        => '([:_0-9a-z-]+)',
	          '{title}'       => '([_0-9a-z-]+)',
	          '{user}'        => '([_0-9a-z-]+)',
	          '{user-id}'     => '([0-9]+)',
	          '{category-id}' => '([0-9]+)',
	          '{category}'    => $category,
	          '{categories}'  => $categories,
	          '{add}'		  => ''
	          ));
	$output = run_filters('htaccess-rules-replace', $output);

return $output;
}

function htaccess_rules_format($output, $result = false){

    $output = run_filters('htaccess-rules-format', $output);
	$output = str_replace('{Month', '{month', $output);
	$output = str_replace('{categories', '{category', $output);
	$output = str_replace('{category-id', '{category', $output);
	$output = preg_replace('/{(.*?):(.*?)}/i', '{\\1}{\\2}', $output);
	$output = str_replace('{add}', '', $output);

	preg_match_all('/\{(.*?)\}/i', $output, $array);

	for ($i = 0; $i < count($array[1]); $i++){
		$result .= ($i ? '&' : '?').(!eregi('=', $array[1][$i]) ? $array[1][$i].'=$'.($i + 1) : $array[1][$i]);
	}

return $result;
}

add_filter('new-advanced-options', 'advanced_options_empty');
add_filter('edit-advanced-options', 'advanced_options_empty');

function advanced_options_empty($story){

	if ($story != 'short' and $story != 'full'){
		return $story;
	}
}
?>
