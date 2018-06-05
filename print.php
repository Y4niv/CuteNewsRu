<?php
include_once 'head.php';

add_filter('news-entry-content', 'link_to_text');

function link_to_text($output){

	$output = preg_replace('/<a href=(\\\"|"|\'{0,1})(.*?)(\\1)(.*?)>(.*?)<\/a>/i', '\\5 ( <span class="link">\\2</span> )', $output);

return $output;
}

$template = 'Print';
$number = 1;
include $cutepath.'/show_news.php';
?>