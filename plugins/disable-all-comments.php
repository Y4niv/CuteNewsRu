<?php
/*
Plugin Name:	Disable All Comments
Plugin URI:		http://cutenews.ru/cat/plugins/
Description:	Disable all comments.
Version:		1.0
Application: 	CuteNews
Author: 		David Carrington
Author URI: 	http://www.brandedthoughts.co.uk
*/

add_filter('news-show-comments','disable_all_comments', 5000);
add_filter('news-allow-comment','disable_all_comments', 5000);

add_filter('news-show-generic','dac_display');

function disable_all_comments($allow, $h){return false;}

function dac_display($entry, $h){
global $output;

	$output = preg_replace('{\[com-link\](.*?)\[\/com-link\]}i', '', $output);
	$output = preg_replace('{\[comheader\](.*?)\[\/comheader\]}i', '', $output);

return $output;
}

?>
