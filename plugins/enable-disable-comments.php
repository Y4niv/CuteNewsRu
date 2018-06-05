<?php
/*
Plugin Name:	Enable/Disable Comments
Plugin URI:		http://cutenews.ru/
Description:	Enable or disable comments for each individual news item.
Version:		2.0
Application: 	CuteNews
Author: 		&#216;ivind Hoel
Author URI: http://appelsinjuice.org/
*/

define('EDC_COMMENTS_XFIELD', 'comments');
define('EDC_DEFAULT_VALUE', 'on');
define('EDC_STOP_DEFAULT_VALUE', 'no');
define('EDC_STOPCOMMENTS_FIELD', 'commentsstop');

add_action('edit-advanced-options', 'edc_checkbox', 10);
add_action('new-advanced-options', 'edc_checkbox', 10);

add_action('new-save-entry', 'edc_save');
add_action('edit-save-entry', 'edc_save');

add_filter('news-show-comments', 'edc_comments');
add_filter('news-show-generic', 'edc_display');

add_filter('add-comment-box', 'edc_comments');
add_filter('add-comment-box', 'edc_stopcomments');

add_filter('template-variables-full', 'edc_addfullvariable');
add_filter('template-variables-active', 'edc_addfullvariable');

# get saved value for an entry (or set default if new)
function edc_getsavedvalue($id){
global $endiscomments, $stopcomments;

	if ($endiscomments != 'on'){$endiscomments = 'off';}
	if ($stopcomments != 'on'){$stopcomments = 'off';}

	$xfields       = new XfieldsData();
	$endiscomments = $xfields -> fetch($id, EDC_COMMENTS_XFIELD);
	$stopcomments  = $xfields -> fetch($id, EDC_STOPCOMMENTS_FIELD);

	if (!$endiscomments){$endiscomments = EDC_DEFAULT_VALUE;}
	if (!$stopcomments){$stopcomments = EDC_STOP_DEFAULT_VALUE;}

	$return = array(
	            'allow' => $endiscomments,
	            'stop'  => $stopcomments,
	            'edit'  => $id
	          );

return $return;
}

#print the advanced options checkboxes
function edc_checkbox($hook) {
global $id, $endiscomments, $stopcomments;

    $echo  = cute_lang('plugins/enable-disable-comments');
	$value = edc_getsavedvalue($id);
	if ($value['allow'] == 'on'){$checked = 'checked="checked"';}
	if ($value['stop'] == 'on'){$checked2 = 'checked="checked"';}

	if ($hook = 'edit-advanced-options'){
		# insert checkbox to close comments here
	}

	return '<fieldset><legend>'.$echo['header'].'</legend>
			<label for="endiscomments"><input type="checkbox" id="endiscomments" name="endiscomments" value="on" '.$checked.' />&nbsp;'.$echo['allow'].'</label>
            <br />
            <label for="stopcomments">
            <input type="checkbox" id="stopcomments" name="stopcomments" value="on" '.$checked2.' />&nbsp;'.$echo['stop'].'</label>
            </fieldset>';

}

# save comment status for an article
function edc_save(){
global $id, $endiscomments, $stopcomments;

	$xfields = new XfieldsData();

	if ($endiscomments != 'on'){$endiscomments = 'off';}
	if ($stopcomments != 'on'){$stopcomments = 'off';}

	$xfields -> set($endiscomments, $id, EDC_COMMENTS_XFIELD);
	$xfields -> set($stopcomments, $id, EDC_STOPCOMMENTS_FIELD);
	$xfields -> save();
}

# parses templates - removes comment links if appropriate
function edc_display(){
global $row, $output;

	$allow = edc_getsavedvalue($row['id']);
	if ($allow['allow'] == 'on' and $row['comments'] > 0){$output = preg_replace('/\[comheader\](.*?)\[\/comheader\]/i', '\\1', $output);}
	else {$output = preg_replace('/\[comheader\](.*?)\[\/comheader\]/i', '', $output);}

return $output;
}

function edc_comments($template){
global $id, $allow_comments;

	$cfg = edc_getsavedvalue($id);

	if ($cfg['allow'] == 'on'){
		$allow_comments = true;
		return $template;
	} else {
		$allow_comments = false;
	}
}

# add the comheader template variable
function edc_addfullvariable($variables){

	$variables["[comheader] ... [/comheader]"] = "Only displayed if there is a comment.";

return $variables;
}

# kills the comment form if comments are stopped
function edc_stopcomments($template){
global $id;

	$cfg = edc_getsavedvalue($id);

	if ($cfg['stop'] != 'on'){
		return $template;
	}
}
?>