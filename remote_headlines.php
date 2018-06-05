<?php
// Как использовать этот модуль.
// Втсавьте этот код:
// <script language="javascript" src="http://mysite.com/path/to/cutenews/remote_headlines.php"></script>
// Можно использовать разное кол-во новостей:
// http://mysite.com/path/to/cutenews/remote_headlines.php?number=NUMBER_OF_NEWS
// Можно использовать и категории:
// http://mysite.com/path/to/cutenews/remote_headlines.php?number=NUMBER_OF_NEWS&category=CAT_ID
// Шаблон для мода по умолчанию remote_headlines


include_once 'head.php';

add_filter('news-entry', 'replace_quotes');

function replace_quotes($output){

	$output = str_replace('\"', '\'', $output);

return $output;
}

$template = 'remote_headlines';
$number = ($number ? $number : 7);
include $cutepath.'/show_news.php';
?>
