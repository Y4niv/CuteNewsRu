<?php
include_once 'head.php';

//----------------------------------
// Восставшие из Зада
//----------------------------------
foreach($_GET as $k => $v){
	$$k = (!$v ? $$k : @htmlspecialchars($v));
}

if (is_array($_CUTE)){
	foreach($_CUTE as $k => $v){
	    $$k = (!$static ? (!$v ? $$k : @htmlspecialchars($v)) : '');
	}
}

foreach($_POST as $k => $v){
	$$k = (!$v ? $$k : @htmlspecialchars($v));
}

if (is_array($static)){
	foreach($vars as $k => $v){
		if ($v != 'static' and $v != 'id' and $v != 'ucat'){
	    	unset($$v);
	    }
	}

	foreach($static as $k => $v){
	    $$k = $v;
	}
}

if (!$sort[0] or !eregi('(A|DE)SC', $sort[1])){
	$sort = array('date', 'DESC');
}

if (eregi('([0-9]{2}):([0-9]{2}):([0-9]{2})', $time)){
	$m_n = array('jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'may' => 5, 'jun' => 6, 'jul' => 7, 'aug' => 8, 'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12);
    $time = explode(':', $time);
    $time = mktime($time[0], $time[1], $time[2], (eregi('[a-z]', $month) ? $m_n[$month] : $month), $day, $year);
}

if (eregi('[a-z]', $category)){
	$category = category_get_id($category);
}

if ($category and !eregi(',', $category)){
	$template = ((!eregi('(rss|print).php', $_SERVER['PHP_SELF']) and !$static and $cat_template[$category]) ? $cat_template[$category] : $template);
}

if ($category){
	foreach (explode(',', str_replace(' ', '', $category)) as $cat){
	    $category_tmp .= category_get_children($cat).',';
	}
}

if (!$number){
	$number = $sql->table_count('news');
}

if (!$template or strtolower($template) == 'default' or is_file($template)){
	include $cutepath.'/data/tpl/Default.tpl';
} else {
	include $cutepath.'/data/tpl/'.$template.'.tpl';
}

$cache_uniq = cache_touch_this();

$allow_categories   = explode(',', chicken_dick($category_tmp, ','));
$allow_edit_comment = false;
$allow_add_comment  = false;
$allow_comment_form = false;
$allow_full_story   = false;
$allow_active_news  = false;
$allow_comments     = false;

if (!$static and ($id or $title or $time)){
	if ($_POST['action'] == 'addcomment'){
		$allow_add_comment = true;
	} else {
		$allow_full_story   = true;
		$allow_comments     = true;
		$allow_comment_form = true;
	}
} else {
	$allow_active_news = true;
}

include $cutepath.'/inc/show.inc.php';

$PHP_SELF = $_SERVER['PHP_SELF'];

if (!cache and !global_cache){
	cache_remover();
}

if ($unset_vars = run_filters('unset', $unset_vars)){
	foreach ($unset_vars as $unset){
		unset($$unset);
	}
}

unset(
	/* пользовательские */
	$template,
	$category,
    $static,
	$number,
	$year,
	$month,
	$day,
	$user,
	$author,
	$skip,
	$sort,
	$cache_uniq,
	$user_query,

	/* мусор */
	$category_tmp,
	$parent,
	$catid,
	$cat,
	$no_prev,
	$no_next,
	$i,
	$prev,
	$count_tmp,
	$unset_vars
);
?>

<!-- Powered by CuteNews.RU | http://cutenews.ru/ | http://forums.cutenewsru.com -->