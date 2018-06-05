<?php
/*
Plugin Name:	Format Switcher
Plugin URI: 	http://cutenews.ru/
Description:	Switch between HTML, Textile and MarkDown.
Version: 		1.0
Application: 	CuteNews
Author: 		David Carrington
Author URI:		http://www.brandedthoughts.co.uk
*/

define('FS_FORMAT_XFIELD', 'fs_format');
define('FS_DEFAULT_FORMAT', 'html_with_br');


add_action('edit-advanced-options', 'fs_FormatSelectBox');
add_action('new-advanced-options', 'fs_FormatSelectBox');
add_action('new-save-entry', 'fs_SaveFormat');
add_action('edit-save-entry', 'fs_SaveFormat');

add_filter('news-entry-content', 'fs_ApplyFormat', 10);
add_filter('news-comment-content', 'fs_ApplyFormat', 10);

function fs_FormatDropDownOptions(){
global $fs_formats, $item_db, $id;

    $echo    = cute_lang('plugins/format-switcher');
	$xfields = new XfieldsData();

	if (!$format = $xfields->fetch($id, FS_FORMAT_XFIELD)){
		$format = ($_POST['fs_format'] ? $_POST['fs_format'] : FS_DEFAULT_FORMAT);
	}

	if ($fs_formats){
		$desc['text']         = $echo['textWithoutBR'];
		$desc['text_with_br'] = $echo['textWithBR'];
		$desc['html']         = $echo['htmlWithoutBR'];
		$desc['html_with_br'] = $echo['htmlWithBR'];

		foreach ($fs_formats as $fs_name => $fs_function){
			$buffer .= '<option value="'.$fs_name.'"'.($format == $fs_name ? ' selected="selected"' : '').'>'.$desc[$fs_name].'</option>';
		}
	}

return $buffer;
}

function fs_FormatSelectBox($hook){

    $echo   = cute_lang('plugins/format-switcher');
	$buffer = '<fieldset><legend>'.$echo['header'].'</legend><select name="fs_format" id="cboFS_Format">'.fs_FormatDropDownOptions().'</select></fieldset>';

return $buffer;
}

function fs_SaveFormat($hook){
global $id;

	$format  = stripslashes($_POST['fs_format']);
	$xfields = new XfieldsData();
	$xfields->set($format, $id, FS_FORMAT_XFIELD);
	$xfields->save();
}

function fs_ApplyFormat($content, $hook){
global $row, $fs_formats;

	if ($_POST['fs_format']){
		$format = $_POST['fs_format'];
	} else {
		// Load all the xfield data
		$xfields = new XfieldsData();

		// Get the Format for the current news ID
		$format = $xfields->fetch($row['id'], FS_FORMAT_XFIELD);
	}

	// Get the function name
	$format_function = $fs_formats[$format];

	if (!$format_function or !function_exists($format_function)){
		$format_function = $fs_formats[FS_DEFAULT_FORMAT];
	}

	// Run the formatting function
	$content = $format_function($content);

return $content;
}


/* Default formats */

$GLOBALS['fs_formats']['text']         = 'fs_Plain';
$GLOBALS['fs_formats']['text_with_br'] = 'fs_Plain_br';
$GLOBALS['fs_formats']['html']         = 'fs_HTML';
$GLOBALS['fs_formats']['html_with_br'] = 'fs_HTML_br';

function fs_Plain($content){

	$content = htmlspecialchars($content);
	$content = str_replace('{nl}', '', $content);

return $content;
}

function fs_Plain_br($content){

	$content = htmlspecialchars($content);
	$content = str_replace('{nl}', '<br />', $content);

return $content;
}

function fs_HTML($content){

	$content = str_replace('{nl}', '', $content);

return $content;
}

function fs_HTML_br($content) {

	$content = str_replace('{nl}', '<br />', $content);

return $content;
}
?>