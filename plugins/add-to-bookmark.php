<?php
/*
Plugin Name:	Bookmarks
Plugin URI:		http://cutenews.ru
Description:	Add <code>$bookmark = true</code> to the include code to show only news which are bookmarked in addnews/editnews.
Version:		2.0
Application:	CuteNews.RU
Author:			&#1051;&#1105;&#1093;&#1072; zloy &#1080; &#1082;&#1088;&#1072;&#1089;&#1080;&#1074;&#1099;&#1081;
Author URI:		http://lexa.cutenews.ru
*/

add_action('head', 'bookmark');

function bookmark(){
global $xfields, $lang;

	// подключаем "хранилисче" плагинных настроек
	// можно сделать это и в bookmark_check(),
	// но ресурсов сожрётся куда больше
	// ведь это будет делаться для каждого поста
	// А так проверяем: если кем-то другим не вызван -
	// то вызываемсами
	if (!is_object($xfields)){
		$xfields = new XfieldsData();
	}

	// если в УРЛе кто-то указывает bookmark=что-то,
	// то подобная херня идёт лесом
	if ($_GET['bookmark']){
		$_GET['bookmark'] = '';
	}

	// подключаем языковой модуль
	$lang = cute_lang('plugins/add-to-bookmark');
}


// добавляем филтр постов
add_filter('also-allow', 'bookmark_check');

function bookmark_check($where){
global $bookmark, $sql, $xfields;

	if ($bookmark){
		for ($id = 0; $id <= $sql->last_insert_id('news', '', 'id'); $id++){
			if ($xfields->fetch($id, 'bookmark') == 'on'){
				$found	 = true;
				$where[] = "id = $id";
				$where[] = 'or';
			}
		}

		if ($found){
			$where[sizeof($where) - 1] = 'and';
		}
	}

return $where;
}

// трём значение переменной для
// избежания проблем с другими
// инклудами show_news.php
add_filter('unset', 'bookmark_unset');

function bookmark_unset($var){

	// имя переменной без знака доллара ($),
	// это важный момент!
	$var[] = 'bookmark';

return $var;
}

// добавляем форму к добавлению и редактированию постов
add_action('new-advanced-options', 'bookmark_AddEdit', 3);
add_action('edit-advanced-options', 'bookmark_AddEdit', 3);

function bookmark_AddEdit(){
global $id, $lang;

	$xfields = new XfieldsData();

return '<fieldset><legend>'.$lang['bookmark'].'</legend><label for="bookmark"><input type="checkbox" id="bookmark" name="bookmark" value="on"'.($xfields->fetch($id, 'bookmark') == 'on' ? ' checked="checked"' : '').'>&nbsp;'.$lang['addTo'].'</label></fieldset>';
}

// записываем настройки
add_action('new-save-entry', 'add2bookmark');
add_action('edit-save-entry', 'add2bookmark');

function add2bookmark(){
global $id;

	// Сохраняем настройки
	$xfields = new XfieldsData();

	if ($_POST['bookmark']){ // если $_POST['bookmark'] не пустой - записываем
		$xfields->set($_POST['bookmark'], $id, 'bookmark');
	} else { // если пустой, то удаляем следы
		$xfields->deletefield($id, 'bookmark');
	}

	$xfields->save();
}

// всю жизнь пройдя до половины, я очутился в сумрачном лесу
?>