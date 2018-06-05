<?php
/*
Plugin Name: 	InsertLinks
Plugin URI:     http://cutenews.ru
Description: 	Automatic insertion of links.
Version: 		1.0
Application: 	CuteNews
*/
// Забыл, у кого и где содрано! Напомните мне :)

add_filter('news-entry-content', 'InsertLinks');
add_filter('news-comment-content', 'InsertLinks');

function InsertLinks($Text){

	$NotAnchor = '(?<!"|href=|href\s=\s|href=\s|href\s=)';
	$Protocol = '(http|ftp|https):\/\/';
	$Domain = '[\w]+(.[\w]+)';
	$Subdir = '([\w\-\.,@?^=%&:\/~\+#]*[\w\-\@?^=%&\/~\+#])?';
	$Expr = '/' . $NotAnchor . $Protocol . $Domain . $Subdir . '/i';
	$Result = preg_replace( $Expr, "<a href=\"$0\" target=\"_blank\">$0</a>", $Text );
	$NotAnchor = '(?<!"|href=|href\s=\s|href=\s|href\s=)';
	$NotHTTP = '(?<!:\/\/)';
	$Domain = 'www(.[\w]+)';
	$Subdir = '([\w\-\.,@?^=%&:\/~\+#]*[\w\-\@?^=%&\/~\+#])?';
	$Expr = '/' . $NotAnchor . $NotHTTP . $Domain . $Subdir . '/i';

return preg_replace($Expr, "<a href=\"http://$0\" target=\"_blank\" rel=\"nofollow\">$0</a>", $Result);
}
?>