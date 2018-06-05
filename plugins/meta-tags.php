<?php
/*
Plugin Name:	Meta tags
Plugin URI:		http://cutenews.ru
Description:	Shows meta tags keywords and description.<br />Use:<br />keywords - <code>&lt;meta name="keywords" content="&lt;?=cn_meta('keywords'); ?&gt;"&gt;</code><br />description - <code>&lt;meta name="description" content="&lt;?=cn_meta('description'); ?&gt;"&gt;</code>
Version:		1.0
Application:	CuteNews
Author:			&#1051;&#1105;&#1093;&#1072; zloy &#1080; &#1082;&#1088;&#1072;&#1089;&#1080;&#1074;&#1099;&#1081;
Author URI:		http://lexa.cutenews.ru
*/

add_action('new-advanced-options', 'metatag_AddEdit');
add_action('edit-advanced-options', 'metatag_AddEdit');

function metatag_AddEdit(){
global $id;

	$xfields = new XfieldsData();
	$return	 = '<fieldset><legend>Meta keywords</legend><textarea name="meta_keywords">'.$xfields->fetch($id, 'meta_keywords').'</textarea></fieldset><fieldset><legend>Meta description</legend><textarea name="meta_description">'.$xfields->fetch($id, 'meta_description').'</textarea></fieldset>';

return $return;
}

add_action('new-save-entry', 'metatag_save');
add_action('edit-save-entry', 'metatag_save');

function metatag_save(){
global $id;

	$xfields = new XfieldsData();
	$xfields->set($_POST['meta_keywords'], $id, 'meta_keywords');
	$xfields->set($_POST['meta_description'], $id, 'meta_description');
	$xfields->save();
}

function cn_meta($meta = 'keywords'){
global $sql, $xfields, $id, $time, $title, $cache_file;
static $uniqid;

	if (!$cache = cute_cache($meta.'-'.str_replace(array('/', '?', '&', '='), '-', chicken_dick($_SERVER['REQUEST_URI'])), $uniqid++)){
		if (!$id and ($title or $time)){
			if ($title){
				$where[] = "url = $title";
			} elseif ($time){
				if (eregi('([0-9]{2}):([0-9]{2}):([0-9]{2})', $time)){
					$m_n  = array('jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'may' => 5, 'jun' => 6, 'jul' => 7, 'aug' => 8, 'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12);
					$time = explode(':', $time);
					$time = mktime($time[0], $time[1], $time[2], (eregi('[a-z]', $month) ? $m_n[$month] : $month), $day, $year);
				}

				$where[] = "date = $time";
			}

			$query = reset(
					 $sql->select(array(
					 'table'  => 'news',
					 'where'  => $where,
					 'select' => array('id')
					 )));

			$id	   = $query['id'];
		}

		if (!is_object($xfields)){
			$xfields = new XfieldsData();
		}

		$cache = $xfields->fetch($id, 'meta_'.$meta);
		file_write($cache_file, $cache);
	}

return $cache;
}
?>