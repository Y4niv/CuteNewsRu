<?php
///////////////////////////////////////////////////////
// Function:	formatsize
// Description: Format the size of given file

function formatsize($file_size){
global $echo;

	if ($file_size >= 1073741824){
		$file_size = (round($file_size / 1073741824 * 100) / 100).' '.$echo['measure']['gigabyte'];
	} elseif ($file_size >= 1048576){
		$file_size = (round($file_size / 1048576 * 100) / 100).' '.$echo['measure']['megabyte'];
	} elseif ($file_size >= 1024){
		$file_size = (round($file_size / 1024 * 100) / 100).' '.$echo['measure']['kilobyte'];
	} else {
		$file_size = $file_size.' '.$echo['measure']['byte'];
	}

return '<nobr>'.$file_size.'</nobr>';
}

///////////////////////////////////////////////////////
// Class:		microTimer
// Description:	calculates the micro time

class microTimer {
	function start() {
	global $starttime;

		$mtime	   = microtime();
		$mtime	   = explode (' ', $mtime);
		$mtime	   = $mtime[1] + $mtime[0];
		$starttime = $mtime;
	}
	function stop() {
	global $starttime;

		$mtime	   = microtime ();
		$mtime	   = explode (' ', $mtime);
		$mtime	   = $mtime[1] + $mtime[0];
		$endtime   = $mtime;
		$totaltime = round (($endtime - $starttime), 5);

	return $totaltime;
	}
}

///////////////////////////////////////////////////////
// Function:	check_login
// Description: Check login information

function check_login($username, $md5_password){
global $member, $members;

	$result = false;
	foreach ($members as $row){
		if ($username == $row['username'] and $md5_password == $row['password']){
			$result = true;
			$member = $row;
		}
	}

return $result;
}

///////////////////////////////////////////////////////
// Function:	cute_query_string
// Description: Format the Query_String for CuteNews purpuses index.php?

function cute_query_string($q_string, $strips, $type = 'get'){

	foreach ($strips as $key){
		$strips[$key] = true;
	}

	foreach(explode('&', $q_string) as $var_peace){
		$parts = explode('=', $var_peace);

		if ($strips[$parts[0]] != true and $parts[0]){
			if ($type == 'post'){
				$my_q .= '<input type="hidden" name="'.$parts[0].'" value="'.$parts[1].'" />';
			} else {
				$my_q .= '&'.$var_peace;
			}
		}
	}

return $my_q;
}

///////////////////////////////////////////////////////
// Function:	showRow

function showRow($title = '', $description = '', $field = ''){
global $i;

	if ($i%2 !== 0 and $title){
		$bg = 'class="enabled"';
	} else {
		$bg = 'class="disabled"';
	}

	echo '<tr '.$bg.'>
			<td width="400" colspan="2" class="opt-title">&nbsp;<b>'.$title.'</b></td>
			<td width="250" rowspan="2" valign="middle" align="left" class="opt-space">'.$field.'</tr>
		 <tr '.$bg.'>
			<td width="20" class="opt-space">&nbsp;</td>
			<td width="400" valign="top" class="opt-desc">'.$description.'</td>
		 </tr>';

	$bg = '';
	$i++;
}

///////////////////////////////////////////////////////
// Function:	makeDropDown
// Description:	Создаёт выподающее меню

function makeDropDown($options, $name, $selected){

	foreach ($options as $value => $description){
		$output .= '<option value="'.$value.'"'.(($selected == $value) ? ' selected ' : '').'>'.$description.'</option>';
	}

return '<select size="1" name="'.$name.'">'.$output.'</select>';
}

///////////////////////////////////////////////////////
// Function:	Flooder
// Description: Flood Protection Function

function flooder($ip, $id){
global $cutepath, $config_flood_time, $sql;

	foreach ($sql->select(array('table' => 'flood')) as $row){
		if (($row['date'] + $config_flood_time) > time() and $row['ip'] == $ip and $row['post_id'] == $id){
		   $result = true;
		} else {
			$result = false;
			$sql->delete(array(
			'table' => 'flood',
			'where' => array("date = $row[date]", 'and', "ip = $row[ip]", 'and', "post_id = $row[post_id]")
			));
		}
	}

return $result;
}

////////////////////////////////////////////////////////
// Function:	msg
// Description: Displays message to user

function msg($type, $title, $text, $back = false){
global $echo;

	echoheader($type, $title);
	echo '<table border="0" cellpading="0" cellspacing="0" width="100%" height="100%"><tr><td>'.$text.($back ? '<br /><br /><a href="'.$back.'">'.$echo['back'].'</a>' : '').'</table>';
	echofooter();
	exit;
}

////////////////////////////////////////////////////////
// Function:	echoheader
// Description: Displays header skin

function echoheader($image, $header_text){
global $PHP_SELF, $is_logged_in, $config_skin, $skin_header, $skin_menu, $skin_prefix, $config_version_name;

	if ($is_logged_in == true){
		$skin_header = str_replace('{menu}', $skin_menu, $skin_header);
	} else {
		$skin_header = str_replace('{menu}', ' &nbsp; '.$config_version_name, $skin_header);
	}

	$skin_header = str_replace('{image-name}', $skin_prefix.$image, $skin_header);
	$skin_header = str_replace('{header-text}', $header_text, $skin_header);
	$skin_header = str_replace('{copyrights}', '<div style="font-size: 9px; text-transform: uppercase">Powered by <a style="font-size: 9px" href="http://cutenews.ru/" target=_blank>'.$config_version_name.' '.$config_version_id.'</a> &copy; 2004-2005 (Original by <a style="font-size: 9px" href="http://cutephp.com/" target="_blank">CutePHP</a>)</div>', $skin_header);

	echo $skin_header;
}

////////////////////////////////////////////////////////
// Function:	echofooter
// Description: Displays footer skin

function echofooter(){
global $PHP_SELF, $is_logged_in, $config_skin, $skin_footer, $skin_menu, $skin_prefix, $config_version_name, $config_version_id;

	if ($is_logged_in == true){
		$skin_footer = str_replace('{menu}', $skin_menu, $skin_footer);
	} else {
		$skin_footer = str_replace('{menu}', ' &nbsp; '.$config_version_name, $skin_footer);
	}

	$skin_footer = str_replace('{image-name}', $skin_prefix.$image, $skin_footer);
	$skin_footer = str_replace('{header-text}', $header_text, $skin_footer);
	$skin_footer = str_replace('{copyrights}', '<div style="font-size: 9px; text-transform: uppercase">Powered by <a style="font-size: 9px" href="http://cutenews.ru/" target=_blank>'.$config_version_name.' '.$config_version_id.'</a> &copy; 2004-2005 (Original by <a style="font-size: 9px" href="http://cutephp.com/" target="_blank">CutePHP</a>)</div>', $skin_footer);

	echo $skin_footer;
}

////////////////////////////////////////////////////////
// Function:	b64dck
// Description: And the duck fly away.
function b64dck(){
	$cr = bd_config('e2NvcHlyaWdodHN9');$shder = bd_config('c2tpbl9oZWFkZXI=');$sfter = bd_config('c2tpbl9mb290ZXI=');
		global $$shder,$$sfter;
	$HDpnlty = bd_config('PGNlbnRlcj48aDE+Q3V0ZU5ld3M8L2gxPjxhIGhyZWY9Imh0dHA6Ly9jdXRlcGhwLmNvbSI+Q3V0ZVBIUC5jb208L2E+PC9jZW50ZXI+PGJyPg==');
	$FTpnlty = bd_config('PGNlbnRlcj48ZGl2IGRpc3BsYXk9aW5saW5lIHN0eWxlPSdmb250LXNpemU6IDExcHgnPlBvd2VyZWQgYnkgPGEgc3R5bGU9J2ZvbnQtc2l6ZTogMTFweCcgaHJlZj0iaHR0cDovL2N1dGVwaHAuY29tL2N1dGVuZXdzLyIgdGFyZ2V0PV9ibGFuaz5DdXRlTmV3czwvYT4gqSAyMDA0ICA8YSBzdHlsZT0nZm9udC1zaXplOiAxMXB4JyBocmVmPSJodHRwOi8vY3V0ZXBocC5jb20vIiB0YXJnZXQ9X2JsYW5rPkN1dGVQSFA8L2E+LjwvZGl2PjwvY2VudGVyPg==');

		if(!stristr($$shder,$cr) and !stristr($$sfter,$cr)){ $$shder = $HDpnlty.$$shder; $$sfter = $$sfter.$FTpnlty; }
}

////////////////////////////////////////////////////////
// Function:	insertSmilies
// Description: insert smilies for adding into news/comments

function insertSmilies($insert_location, $break_location = false){
global $config_http_script_dir, $config_smilies;

	$smilies = explode(',', $config_smilies);
	foreach ($smilies as $smile){
		$i++;

		$output .= '<a href="javascript:insertext(\':'.trim($smile).':\', \'\', \''.$insert_location.'\')"><img style="border: none;" alt="'.trim($smile).'" src="'.$config_http_script_dir.'/data/emoticons/'.trim($smile).'.gif" /></a>';

		if ($break_location and $i%$break_location == 0){
			$output .= '<br />';
		} else {
			$output .= '&nbsp;';
		}
	}

return $output;
}

////////////////////////////////////////////////////////
// Function:	replace_comments
// Description: Replaces comments charactars
function replace_comment($way, $sourse){
global $config_allow_html_in_comments, $config_http_script_dir, $config_smilies;

	if ($way == 'add'){
		$find	 = array("\n", "\r");
		$replace = array('<br />', '');
		$sourse	 = htmlspecialchars($sourse);

		if (!get_magic_quotes_gpc()){
			$sourse = addslashes($sourse);
		}

	} elseif ($way == 'show'){
		$find	 = array('&amp;');
		$replace = array('&');
		$sourse = stripslashes($sourse);

		foreach (explode(',', $config_smilies) as $smile){
			$find[]	   = ':'.trim($smile).':';
			$replace[] = '<img style="border: 0px; vertical-align: middle;" alt="'.trim($smile).'" src="'.$config_http_script_dir.'/data/emoticons/'.trim($smile).'.gif" />';
		}
	} elseif ($way == 'admin'){
		$find	 = array('<br />');
		$replace = array("\n");
		$sourse	 = unhtmlentities($sourse);
		$sourse	 = stripslashes($sourse);
	}

return str_replace($find, $replace, trim($sourse));
}

////////////////////////////////////////////////////////
// Function:	replace_news
// Description: Replaces news charactars

function replace_news($way, $sourse, $replce_n_to_br = true, $use_html = true){
global $config_allow_html_in_news, $config_http_script_dir, $config_smilies;

	if ($way == 'show'){
		$find	 = array('{nl}');
		$replace = array('<br />');
		$sourse	 = stripslashes($sourse);

		foreach (explode(',', $config_smilies) as $smile){
			$find[]	   = ':'.trim($smile).':';
			$replace[] = '<img style="border: 0px; vertical-align: middle;" alt="'.trim($smile).'" src="'.$config_http_script_dir.'/data/emoticons/'.trim($smile).'.gif" />';
		}
	} elseif($way == 'add'){
		$find	 = array('|', "\r", "\n");
		$replace = array('&#124', '', '{nl}');

		if (!get_magic_quotes_gpc()){
			$sourse = addslashes($sourse);
		}
	} elseif ($way == 'admin'){
		$find	 = array('{nl}', '<br>', '<br />');
		$replace = array("\n", "\n", "\n");
		$sourse	 = stripslashes($sourse);
	}

return str_replace($find, $replace, trim($sourse));
}

function bd_config($str){
return base64_decode($str);
}

////////////////////////////////////////////////////////
// Function:	echo_r

function echo_r($array){
	ob_start();
	if (is_bool($array)){
		echo ($array ? 'true' : 'false');
	} else {
		print_r($array);
	}

	$echo = ob_get_contents();
	ob_clean();

	echo highlight_string($echo, true);
}

////////////////////////////////////////////////////////
// Function:	inserttag
// Description: Вставка списка тэгов при добавлении/редактировании новостей

function inserttag($insert_location){
global $config_http_script_dir;
		$output = "
		<div class=\"tags\"><a href=\"javascript:insertext('<br />','','$insert_location')\" title='Linebreak'><img src=".$config_http_script_dir."/skins/images/tags/br.gif border=0 align=middle></a>
		<a href=\"javascript:insertext('<hr />','','$insert_location')\" title='Horizontal line'><img src=".$config_http_script_dir."/skins/images/tags/hr.gif border=0 align=middle></a>
		<a href=\"javascript:insertext('<p>','</p>','$insert_location')\" title='Paragraph'><img src=".$config_http_script_dir."/skins/images/tags/p.gif border=0 align=middle></a>
		<a href=\"javascript:insertext('<b>','</b>','$insert_location')\" title='Bold'><img src=".$config_http_script_dir."/skins/images/tags/b.gif border=0 align=middle></a>
		<a href=\"javascript:insertext('<i>','</i>','$insert_location')\" title='Italic'><img src=".$config_http_script_dir."/skins/images/tags/i.gif border=0 align=middle></a>
		<a href=\"javascript:insertext('<u>','</u>','$insert_location')\" title='Underline'><img src=".$config_http_script_dir."/skins/images/tags/u.gif border=0 align=middle></a>
		<a href=\"javascript:insertext('<s>','</s>','$insert_location')\" title='Linethrough'><img src=".$config_http_script_dir."/skins/images/tags/s.gif border=0 align=middle></a>
		<a href=\"javascript:insertext('<sub>','</sub>','$insert_location')\" title='Subscript'><img src=".$config_http_script_dir."/skins/images/tags/sub.gif border=0 align=middle></a>
		<a href=\"javascript:insertext('<sup>','</sup>','$insert_location')\" title='Superscript'><img src=".$config_http_script_dir."/skins/images/tags/sup.gif border=0 align=middle></a>
		<a href=\"javascript:insertext('<font color=&quot;&quot;>','</font>','$insert_location')\" title='Font color'><img src=".$config_http_script_dir."/skins/images/tags/color.gif border=0 align=middle></a>
		<a href=\"javascript:insertext('<font size=&quot;&quot;>','</font>','$insert_location')\" title='Font size'><img src=".$config_http_script_dir."/skins/images/tags/size.gif border=0 align=middle></a>
		<a href=\"javascript:insertext('<ul>','</ul>','$insert_location')\" title='List'><img src=".$config_http_script_dir."/skins/images/tags/ul.gif border=0 align=middle></a>
		<a href=\"javascript:insertext('<li>','</li>','$insert_location')\" title='List element'><img src=".$config_http_script_dir."/skins/images/tags/li.gif border=0 align=middle></a>
		<a href=\"javascript:insertext('<a href=&quot;http://&quot; target=&quot;_blank&quot;>','</a>','$insert_location')\" title='URL'><img src=".$config_http_script_dir."/skins/images/tags/url.gif border=0 align=middle></a>
		<a href=\"javascript:insertext('<a href=&quot;mailto:&quot;>','</a>','$insert_location')\" title='Email'><img src=".$config_http_script_dir."/skins/images/tags/mailto.gif border=0 align=middle></a>
		<a href=\"#\" onclick=\"window.open('$PHP_SELF?mod=images&area=$insert_location', '_Addimage', 'height=450,resizable=yes,scrollbars=yes,width=500');return false;\" target=\"_Addimage\"><img src=".$config_http_script_dir."/skins/images/tags/img.gif border=0 align=middle></a>
		<a href=\"javascript:insertext('<div align=&quot;&quot;>','</div>','$insert_location')\" title='Align'><img src=".$config_http_script_dir."/skins/images/tags/align.gif border=0 align=middle></a></div>";

return $output;
}

////////////////////////////////////////////////////////
// Function:	cute_mail Version: 1.0
// Description: Send mail with cutenews

function cute_mail($to,$subject,$message){
global $config_version_name, $config_version_id;

	if (!$config_admin_mail or !$config_admin_mail){
		$mail_from = 'no-reply@'.str_replace('www.', '', $_SERVER['SERVER_NAME']);
	} else {
		$mail_from = $config_admin_mail;
	}

	$headers .= 'From: '.$mail_from."\n";
	$headers .= 'Reply-to: '.$mail_from."\n";
	$headers .= 'Return-Path: '.$mail_from."\n";
	$headers .= 'Message-ID: <'.md5(uniqid(time())).'@'.$_SERVER['SERVER_NAME'].">\n";
	$headers .= 'MIME-Version: 1.0'."\n";
	$headers .= 'Content-type: text/plain; charset=iso-8859-2'."\n";
	$headers .= 'Content-transfer-encoding: 7bit'."\n";
	$headers .= 'Date: '.gmdate('D, d M Y H:i:s', time())."\n";
	$headers .= 'X-Priority: 3'."\n";
	$headers .= 'X-MSMail-Priority: Normal'."\n";
	$headers .= 'X-Mailer: '.$config_version_name.' '.$config_version_id."\n";
	$headers .= 'X-MimeOLE: '.$config_version_name.' '.$config_version_id."\n";

	mail($to, $subject, $message, $headers);
}

////////////////////////////////////////////////////////
// Function:	chmoddir
// Description: функция меняет права всем вложенным папкам и файлам внутри папки, возвращает true в случае удачи и false, если сменить права папке не удалось
// Original:		http://forum.dklab.ru/php/advises/FaylovieFunktsii.html

function chmoddir($dir, $mod){
	$fdir = opendir($dir);
	while ($file = readdir($fdir)){
		if ($file != '.' and $file != '..'){
			if (!is_dir($dir.'/'.$file)){
				chmod($dir.'/'.$file, $mod);
			} else {
				chmoddir($dir.'/'.$file, $mod);
			}
		}
	}
	closedir($dh);

	if (chmod($dir, $mod)){
		return true;
	} else {
		return false;
	}
}

////////////////////////////////////////////////////////
// Function:	цэж_массив
// Description: дети, это кака

function c_array($action, $sort = ''){
global $sql;

	if (is_array($sort)){
		$query = array('table' => $action, 'orderby' => $sort);
	} else {
		$query = array('table' => $action);
	}

	foreach ($sql->select($query) as $k => $v){
		$result[] = implode('|', $v);
	}

return ($result ? $result : array());
}

////////////////////////////////////////////////////////
// Function:	chicken_dick
// Description: отделение мух от супа

function chicken_dick($chicken, $dick = '/'){

	$chicken = preg_replace('/^(['.preg_quote($dick, '/').']+)/', '', $chicken);
	$chicken = preg_replace('/(['.preg_quote($dick, '/').']+)/', $dick, $chicken);
	$chicken = preg_replace('/(['.preg_quote($dick, '/').']+)$/', '', $chicken);

return $chicken;
}

////////////////////////////////////////////////////////
// Function:	file_write
// Description: запись в фаил

function file_write($fopen = '', $fwrite = '', $clear = false, $chmod = false){

	if (!$chmod){
		$chmod = chmod;
	}

	if ($clear){
		$fwrite = str_replace('	 ', '', str_replace("\r\n", '', $fwrite));
	}

	$fp = fopen($fopen, 'wb+');
	fwrite($fp, $fwrite);
	@chmod($fopen, $chmod);
	fclose($fp);
}

////////////////////////////////////////////////////////
// Function:	file_read
// Description: чтение из фаила

function file_read($path){

	if (!filesize($path)){
		return false;
	}

	$fp = fopen($path, 'r');
	$fo = fread($fp, filesize($path));
	fclose($fp);

return $fo;
}

////////////////////////////////////////////////////////
// Function:	cute_parse_url
// Description: аналог parse_url(), но выдаёт чуть больше и немного иначе

function cute_parse_url($url){
global $DOCUMENT_ROOT;

	$url		 = parse_url($url);
	$url['path'] = chicken_dick($url['path']);
	$url['abs']	 = $DOCUMENT_ROOT.'/'.$url['path'];

	if (is_file($url['abs'])){
		$url['file'] = end($url['file'] = explode('/', $url['path']));
		$url['path'] = chicken_dick(preg_replace('/'.$url['file'].'$/i', '', $url['path']));
		$url['abs']	 = $DOCUMENT_ROOT.'/'.$url['path'];
	}

return $url;
}

////////////////////////////////////////////////////////
// Function:	cute_get_link
// Description: формирование УРЛов

function cute_get_link($arr, $type = 'post', $format = 'home'){
global $PHP_SELF, $QUERY_STRING, $allow_full_story, $xfields, $config_http_home_url, $config_http_script_dir, $config_rufus, $user_id;

	# Чибурашко где-то рядом!
	$for = parse_ini_file(rootpath.'/data/urls.ini', true);

	if (!is_array($arr)){
		global $row;

		$string = explode('/', $arr);
		$type	= end($string);
		unset($string[(count($string) - 1)]);
		$format = join('/', $string);
		$arr	= $row;
	}

	if (!is_object($xfields)){
		$xfields = new XfieldsData();
	}

	if ($config_rufus == 'yes'){
		$rufus = true;
	} else {
		$rufus = false;
	}

	if (!$arr['date']){
		$arr['category'] = $arr['id'];
	}

	if (!$arr['author']){
		$arr['author']	= $arr['username'];
		$arr['user_id'] = $arr['id'];
	} else {
		$arr['user_id'] = $user_id[$arr['author']];
	}

	if (!eregi('{(.*)}', $format)){
		$for[$type] = $for[$format][$type];
	} else {
		$for[$type] = $format;
	}

	//if ($for[$type] and $xfields->fetch($arr['id'], 'url_format') == 'cat'){
		//$type = 'post2';
	//}

	$cat	= reset($cat = explode(',', $arr['category']));
	$link	= end(run_filters('cute-get-link', array('arr' => $arr, 'link' => $for[$type])));
	$link	= preg_replace('/{(.*?):(.*?)}/i', '{\\1}', $link);
	$link	= strtr($link, array(
			  '{add}'		  => '',
			  '{id}'		  => $arr['id'],
			  '{year}'		  => date('Y', $arr['date']),
			  '{month}'		  => date('m', $arr['date']),
			  '{Month}'		  => strtolower(date('M', $arr['date'])),
			  '{day}'		  => date('d', $arr['date']),
			  '{time}'		  => date('H:i:s', $arr['date']),
			  '{title}'		  => ($arr['url'] ? $arr['url'] : totranslit($arr['title'])),
			  '{user}'		  => totranslit($arr['author']),
			  '{user-id}'	  => $arr['user_id'],
			  '{category-id}' => ($cat ? $cat : '0'),
			  '{category}'	  => ($cat ? end($category = explode('/', category_get_link($cat))) : 'none'),
			  '{categories}'  => ($cat ? category_get_link($cat) : 'none')
			  ));
	$url	= cute_parse_url($config_http_home_url);
	$config = cute_parse_url($config_http_script_dir);
	$query	= cute_query_string($QUERY_STRING, array('category', 'skip', 'subaction', 'id', 'ucat', 'year', 'month', 'day', 'user', 'page', 'search', 'do', 'PHPSESSID', 'title', 'time'));

	if (!$rufus){
		$result = ($format == 'home' ? ($_SERVER['PHP_SELF'] != $PHP_SELF ? $PHP_SELF : $url['path'].'/'.$url['file']) : ((is_dir($url['abs'].'/'.$format) or is_file($url['abs'].'/'.$format)) ? $url['path'].'/'.$format : $config['path'].'/'.$format.'.php'));
	} else {
		$result = $url['path'];
	}

	$result = chicken_dick($result.'/'.$link).preg_replace('/^(&|&amp;)/i', '?', $query);
	$result = str_replace('/?', '?', $result);

return $url['scheme'].'://'.$url['host'].($url['port'] ? ':'.$url['port'] : '').'/'.$result;
}

////////////////////////////////////////////////////////
// Function:	category_get_link
// Description: получает ссылки похуй веники какой вложенности

function category_get_link($id, $link = ''){
global $cat_url, $cat_parent;

	if ($cat_url[$id]){
		$link = $cat_url[$id].($link ? '/'.$link : '');
	}

	if ($cat_parent[$id]){
		$link = category_get_link($cat_parent[$id], $link);
	}

return chicken_dick($link);
}

////////////////////////////////////////////////////////
// Function:	category_get_children
// Description: получает "детей" категории

/*function category_get_children($id){
global $cat_parent, $categoty;

	foreach ($cat_parent as $cat_id => $parent){
		if ($parent == $id){
			$categories .= category_get_children($cat_id).','.$id.','.$cat_id;
		}
	}

return ($categories ? chicken_dick($categories, ',') : $id);
}*/
function category_get_children($id, $withid = true, $limit = 0){
global $cat_parent;
static $end = 1, $result = array();

	$categories_dummy = $cat_parent;

	if ($id === ''){
		return false;
	}

	if ($withid){
		$result[] = $id;
	}

	foreach ($categories_dummy as $cat_id => $row){
		if ($row == $id){
			$result[] = $cat_id;

			if ($limit - $end){
				$result[] = category_get_children($cat_id, $limit);
			}
		}
	}

	$end++;

	$return = $result;
	$result = array();

return join(',', $return);
}

////////////////////////////////////////////////////////
// Function:	category_get_title
// Description: возвращает имя функции и имена её родителей

function category_get_title($id, $separator = ' &raquo; ', $title = ''){
global $cat_name, $cat_parent;

	if ($cat_name[$id]){
		$title = $cat_name[$id].($title ? $separator.$title : '');
	}

	if ($cat_parent[$id]){
		$title = category_get_title($cat_parent[$id], $separator, $title);
	}

return chicken_dick($title);
}

////////////////////////////////////////////////////////
// Function:	category_get_tree
// Description: возвращает дерево категорий
function category_get_tree($prefix = '', $tpl = '{name}', $no_prefix = true, $id = 0, $level = 0){
global $sql, $PHP_SELF;
static $johnny_left_teat;

	$level++;

	foreach ($sql->select(array('table' => 'categories', 'where' => array("parent = $id"), 'orderby' => array('id', 'ASC'))) as $row){
		$find = array('/{id}/i', '/{name}/i', '/{url}/i', '/{icon}/i', '/{template}/i', '/{prefix}/i', '/\[php\](.*?)\[\/php\]/ie');
		$repl = array($row['id'], $row['name'], $row['url'], ($row['icon'] ? '<img src="'.$row['icon'].'" alt="'.$row['icon'].'" border="0" align="absmiddle">' : ''), $row['template'], (($row['parent'] or !$no_prefix) ? $prefix : ''), '\\1');
		$johnny_left_teat .= ($no_prefix ? preg_replace('/('.$prefix.'{1})$/i', '', str_repeat($prefix, $level)) : str_repeat($prefix, $level));
		$johnny_left_teat .= preg_replace($find, $repl, $tpl);
		category_get_tree($prefix, $tpl, $no_prefix, $row['id'], $level);
	}

return $johnny_left_teat;
}

////////////////////////////////////////////////////////
// Function:	category_get_id
// Description: получает ID категории из её УРЛа

function category_get_id($cat){
global $sql;

	$cat	  = explode('/', chicken_dick($cat));
	$parent	  = reset($sql->select(array(
	'table' => 'categories',
	'where' => array(
			   'name = '.$cat[0],
			   'or',
			   'url = '.$cat[(count($cat) - 2)]
			   )
	)));
	$category = reset($sql->select(array(
	'table' => 'categories',
	'where' => array(
			   'name = '.$cat[0],
			   'and',
			   'parent = '.($parent['id'] ? $parent['id'] : 0),
			   'or',
			   'url = '.$cat[(count($cat) - 1)],
			   'and',
			   'parent = '.($parent['id'] ? $parent['id'] : 0)
			   )
	)));
	$category = ($category ? $category['id'] : $parent['id']);

return $category;
}

////////////////////////////////////////////////////////
// Function:	cute_that
// Description: листает стили

function cute_that($return1 = 'class="enabled"', $return2 = 'class="disabled"', $every = 2){
static $ggg;

	$ggg++;

	if ($ggg%$every == 0){
		return $return1;
	} else {
		return $return2;
	}
}

////////////////////////////////////////////////////////
// Function:	cache_remover
// Description: какачистильщик

function cache_remover($alone = ''){

	$fdir = opendir(rootpath.'/cache');
	while ($file = readdir($fdir)){
		if ($file != '.' and $file != '..' and $file != '.htaccess'){
			if ($alone){
				if (eregi($alone.'(.*).(short|full).', $file)){
					@unlink(rootpath.'/cache/'.$file);
				}

				if (!eregi('.(short|full).', $file)){
					@unlink(rootpath.'/cache/'.$file);
				}
			}

			if (!$alone){
				@unlink(rootpath.'/cache/'.$file);
			}
		}
	}

return true;
}

////////////////////////////////////////////////////////
// Function:	cute_cache
// Description: какамэйкер

function cute_cache($file, $uniqid = '', $ext = ''){
global $cache_file, $template;

	$cache_file = rootpath.'/cache/'.$file.($template ? '-'.$template : '').($uniqid ? '('.$uniqid.')' : '').($ext ? '.'.$ext : '').'.tmp';

	if (file_exists($cache_file) and cache){
		return file_read($cache_file);
	}

return false;
}

////////////////////////////////////////////////////////
// Function:	cache_touch_this

function cache_touch_this(){
static $cache_touch_this;

	$cache_touch_this++;

return ($cache_touch_this == 1 ? '' : $cache_touch_this);
}

////////////////////////////////////////////////////////
// Function:	cute_lang
// Description: выбор языкового модуля

function cute_lang($module = ''){
global $mod, $config_lang;

	if (!file_exists($global = rootpath.'/inc/lang/'.$config_lang.'/global.ini')){
		$global = rootpath.'/inc/lang/en/global.ini';
	}

	if (!file_exists($local = rootpath.'/inc/lang/'.$config_lang.'/'.($module ? $module : $mod).'.ini')){
		$local = rootpath.'/inc/lang/en/'.($module ? $module : $mod).'.ini';
	}

	if (file_exists($global)){
		$lang = parse_ini_file($global, true);
	}

	if (file_exists($local)){
		$lang = array_merge($lang, parse_ini_file($local, true));
	}

return $lang;
}

////////////////////////////////////////////////////////
// Function:	md5x
// Description: когда мы говорим "х" мы подразумеваем хуй

function md5x($str){

	$str = md5(md5($str));

return $str;
}

////////////////////////////////////////////////////////
// Function:	iconv
// Description: если не установлена библиотека перекодировок

if (!function_exists('iconv')){
	function iconv($in_charset, $out_charset, $str){
	return preg_replace_callback('/([\xC0-\xFF\xA8\xB8])/', 'win2utf_char', $str);
	}

	function win2utf_char($c){

		list(,$c) = $c;
		if ($c == "\xA8"){return "\xD0\x81";}
		if ($c == "\xB8"){return "\xD1\x91";}
		if ($c >= "\xC0" and $c <= "\xEF"){return "\xD0".chr(ord($c) - 48);}
		if ($c >= "\xF0"){return "\xD1".chr(ord($c) - 112);}

	return $c;
	}
}

////////////////////////////////////////////////////////
// Function:	unhtmlentities
// Description:	возвращение после htmlentities() и htmlspecialchars()

function unhtmlentities($string){

	$trans_tbl = get_html_translation_table(HTML_ENTITIES);
	$trans_tbl = array_flip($trans_tbl);

return strtr($string, $trans_tbl);
}

////////////////////////////////////////////////////////
// Function:	namespace
// Description:	добавляет порядковый номер у "УРЛу"

function namespace($str){
global $sql, $mod;

	foreach ($sql->select(array('table' => 'news')) as $row){
		if (@preg_match("/$str([0-9]+)?/i", $row['url'])){
			$result[] = $row['id'];
		}
	}

	$count = count($result);

	if ($mod == 'addnews'){
		$count++;
	}

return totranslit($str.(($count and $count != 1) ? ' '.$count : ''));
}
?>