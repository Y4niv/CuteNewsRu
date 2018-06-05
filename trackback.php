<?php
include_once 'head.php';

foreach($_GET as $k => $v){
	$$k = (!$v ? $$k : @htmlspecialchars($v));
}

if (is_array($_CUTE)){
	foreach($_CUTE as $k => $v){
	    $$k = (!$static ? (!$v ? $$k : @htmlspecialchars($v)) : '');
	}
}

function trackback_response($error = 0, $error_message = ''){
global $echo;

	if ($error){
		echo '<?xml version="1.0" encoding="'.$echo['charset'].'"?'.">\n";
		echo "<response>\n";
		echo "<error>1</error>\n";
		echo "<message>$error_message</message>\n";
		echo "</response>";
	} else {
		echo '<?xml version="1.0" encoding="'.$echo['charset'].'"?'.">\n";
		echo "<response>\n";
		echo "<error>0</error>\n";
		echo "</response>";
	}

	exit;
}

if (eregi('([0-9]{2}):([0-9]{2}):([0-9]{2})', $time)){
	$m_n = array('jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'may' => 5, 'jun' => 6, 'jul' => 7, 'aug' => 8, 'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12);
    $time = explode(':', $time);
    $time = mktime($time[0], $time[1], $time[2], (eregi('[a-z]', $month) ? $m_n[$month] : $month), $day, $year);
}

$where = array();

if ($title){
    $where[] = "url = $title";
} elseif ($time){
    $where[] = "date = $time";
} elseif ($id){
    $where[] = "id = $id";
}

$query = reset($query = $sql->select(array('table' => 'news', 'where' => $where)));

if (!$_POST){
	trackback_response(1, 'XML-RPC server accepts POST requests only.');
} elseif (!is_array($query)){
	trackback_response(1, 'Sorry, post not found.');
}

foreach($_POST as $k => $v){
	$$k = (!$v ? $$k : @htmlspecialchars($v));
}

if (function_exists('mb_convert_encoding')){
	$charset   = ($charset ? $charset : 'auto');
	$blog_name = mb_convert_encoding($blog_name, $echo['charset'], $charset);
	$title     = mb_convert_encoding($title, $echo['charset'], $charset);
	$excerpt   = mb_convert_encoding($excerpt, $echo['charset'], $charset);
} elseif (function_exists('iconv')){
		$charset   = ($charset ? $charset : 'utf-8');
        $blog_name = iconv($charset, $echo['charset'], $blog_name);
        $title     = iconv($charset, $echo['charset'], $title);
        $excerpt   = iconv($charset, $echo['charset'], $excerpt);
}

$blog_name    = ($blog_name ? $blog_name : 'no name');
$url          = ($url ? $url : 'none');
$excerpt      = ($excerpt ? $excerpt : 'ugly idiot');
$xfields      = new XFieldsData();
$post         = $xfields->fetch($query['id'], 'trackback');
$post[time()] = array(
				'blog_name' => replace_comment('add', $blog_name),
				'url'       => str_replace('&', '&amp;', $url),
				'host'      => $_SERVER['REMOTE_ADDR'],
				'title'     => replace_comment('add', $title),
				'excerpt'   => replace_comment('add', preg_replace("/(.*?)\n\n..../i", '\\1', $excerpt)),
				'charset'   => $charset
				);
$xfields->set($post, $query['id'], 'trackback');
$xfields->save();

header('Content-Type: text/xml;');
trackback_response();
?>