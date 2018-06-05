<?php
/*
Plugin Name:	Template for alone news
Plugin URI:		http://cutenews.ru
Description:	Новость может быть с любым шаблоном.<br /><br />Template for alone news.
Version:		1.1
Author:			&#1051;&#1105;&#1093;&#1072; zloy &#1080; &#1082;&#1088;&#1072;&#1089;&#1080;&#1074;&#1099;&#1081;
Author URI:		http://lexa.cutenews.ru
*/

// Cartman find you and kill you!

add_action('new-advanced-options', 'change_template');
add_action('edit-advanced-options', 'change_template');

function change_template(){
global $id;

	$xfields = new XfieldsData();
	$result	 = '<select size="1" name="template"><option value="">...</option>';
	$echo	 = cute_lang('plugins/tepmplate-for-news');
	$handle	 = opendir(rootpath.'/data/tpl');
	while ($file = readdir($handle)){
		$template = substr($file, 0, -4);

		if (substr($file, -3) == 'tpl'){
			$result .= '<option value="'.$template.'"'.($xfields->fetch($id, 'tamplate') == $template ? ' selected' : '').'>'.$template.'</option>';
		}
	}

	$result .= '</select>';
	$result	 = '<fieldset><legend>'.$echo['header'].'</legend>'.$result.'</fieldset>';

return $result;
}

add_action('new-save-entry', 'save_template');
add_action('edit-save-entry', 'save_template');

function save_template(){
global $id;

	$xfields = new XfieldsData();
	$xfields->set($_POST['template'], $id, 'tamplate');
	$xfields->save();
}

add_filter('news-show-generic', 'apply_template');

function apply_template(){
global $row, $output, $xfields, $allow_full_story, $allow_add_comment, $static;
global $template_comment, $template_form, $template_prev_next, $template_cprev_next, $template_dateheader;

	if (!is_object($xfields)){
		$xfields = new XfieldsData();
	}

	if ($template = $xfields->fetch($row['id'], 'tamplate') and !$static and !eregi('rss.php', $_SERVER['PHP_SELF'])){
		include rootpath.'/data/tpl/'.$template.'.tpl';

		if ($allow_full_story or $allow_add_comment){
			$output = $template_full;
		} else {
			$output = $template_active;
		}
	}

return $output;
}
?>