<?php
/*
Plugin Name:	Commenters
Plugin URI:     http://english.cutenews.ru/forum
Description:	Displays all commenters for each article. 
Version:		1.0
Author:			FI-DD
Author URI:		http://english.cutenews.ru/forum/profile.php?mode=viewprofile&u=2
*/

function find_commenters($newsid, $linebreak, $separator) {
global $sql;

$i = 0;
	foreach ($sql->select(array('table' => 'comments', 'where' => array("post_id = $newsid"))) as $row) {
		if (!stristr($result, $row['author'])) {
			$i++;
			if($row['mail'] != "none") {
								
				if( preg_match("/^[\.A-z0-9_\-]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/", $row['mail']))
					{$url_target = ""; $mail_or_url = "mailto:";}
				else {
					$url_target = "target=\"_blank\"";
					$mail_or_url = "";
					if(substr($row['mail'],0,3) == "www") $mail_or_url = "http://";
				}
				$result .= '<a '.$url_target.' href="'.$mail_or_url.stripslashes($row['mail']).'">'.stripslashes($row['author']).'</a>'.$separator;
			}
			else $result .= $row['author'].$separator; 
			if (fmod($i, $linebreak)=="0") $result .= "<br />";
		}
	}
	if (fmod($i, $linebreak)=="0") $result = substr($result, 0, -(strlen($separator)+6));
	
	else $result = substr($result, 0, -strlen($separator));
	
	return $result;
}

add_filter('news-entry', 'commenters');
add_filter('news-comment', 'commenters');

function commenters(){
global $output, $row, $sql;

$output = preg_replace("#\{commenters:(.*):(.*)\}#e", "find_commenters('".$row['id']."', '\\1', '\\2')", $output);

$count = count($sql->select(array('table' => 'comments', 'where' => array("post_id = ".$row['id'].""))));

if ($count > 0) {
	$output = str_replace('[commenters-header]', '', $output);
	$output = str_replace('[/commenters-header]', '', $output);
}
else $output = preg_replace("#\[commenters-header\](.*?)\[/commenters-header\]#i", "", $output);
return $output;
}

add_filter('template-variables-active', 'template_commenters');
add_filter('template-variables-full', 'template_commenters');

function template_commenters($template){

	$template['{commenters:N:S}'] = 'Displays N commenters per line (0 for all in one line). S = separator';
	$template['[commenters-header] and [/commenters-header]'] = 'Shows the header (only if there are commenters)';

return $template;
}

?>