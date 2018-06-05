<?php
/*
Plugin Name:	Ago
Plugin URI:     http://cutenews.ru
Description:	Displays what time ago a comment was made. 
Version:		1.0
Author:			Дмитрий Смирнов
Author URI:		http://nudnik.ru
*/

// Картман найдёт тебя, убъёт тебя и выебет тебя!

function sklonenie($gimme, $russ){

    $ummy = preg_replace('[([0-9]{1})]', '|\\1', $gimme);
	$ummy = explode('|', $ummy);
	$ummy = array_reverse($ummy);
	$russ = explode('/', $russ);
	if ($ummy[0] == 1 and $ummy[1] != 1){$echo = $russ[0];}
	elseif ($ummy[0] >= 2 and $ummy[0] <= 4 and $ummy[1] != 1){$echo = $russ[1];}
	else {$echo = $russ[2];}

return $gimme.' '.$echo;
}

function vycheslenie($sec){

	if ($sec > 1000000000 or $sec < 0){
		return '';
	}
	if ($sec < 60){
	    $tmp   = $sec;
	    $last .= ' '.sklonenie($tmp, 'Second/Seconds/Seconds');
	}
	if ($sec >= 86400){
	    $days  = true;
	    $tmp   = floor($sec/86400);
	    $sec   = $sec-$tmp*86400;
	    $last .= ' '.sklonenie($tmp, 'Day/Days/Days');
	}
	if ($sec >= 3600){
	    $tmp   = floor ($sec/3600);
	    $sec   = $sec-$tmp*3600;
	    $last .= ' '.sklonenie($tmp, 'Hour/Hours/Hours');
	}
	if ($days == false and $sec >= 60){
	    $tmp   = floor($sec/60);
	    $sec   = $sec-$tmp*60;
	    $last .= ' '.sklonenie($tmp, 'Minute/Minutes/Minutes');
	}
	if ($last != ''){
		$last .= ' ago';
	}

return $last;
}

add_filter('news-entry', 'ago');
add_filter('news-comment', 'ago');

function ago(){
global $output, $row;

	$output = str_replace('{ago}', vycheslenie(time() - $row['date']), $output);

return $output;
}

add_filter('template-variables-active', 'template_ago');
add_filter('template-variables-full', 'template_ago');
add_filter('template-variables-comments', 'template_ago');

function template_ago($template){

	$template['{ago}'] = 'Displays what time ago a comment was made.';

return $template;
}
?>