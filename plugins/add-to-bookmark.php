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

	// ���������� "����������" ��������� ��������
	// ����� ������� ��� � � bookmark_check(),
	// �� �������� ������� ���� ������
	// ���� ��� ����� �������� ��� ������� �����
	// � ��� ���������: ���� ���-�� ������ �� ������ -
	// �� ������������
	if (!is_object($xfields)){
		$xfields = new XfieldsData();
	}

	// ���� � ���� ���-�� ��������� bookmark=���-��,
	// �� �������� ����� ��� �����
	if ($_GET['bookmark']){
		$_GET['bookmark'] = '';
	}

	// ���������� �������� ������
	$lang = cute_lang('plugins/add-to-bookmark');
}


// ��������� ����� ������
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

// ��� �������� ���������� ���
// ��������� ������� � �������
// ��������� show_news.php
add_filter('unset', 'bookmark_unset');

function bookmark_unset($var){

	// ��� ���������� ��� ����� ������� ($),
	// ��� ������ ������!
	$var[] = 'bookmark';

return $var;
}

// ��������� ����� � ���������� � �������������� ������
add_action('new-advanced-options', 'bookmark_AddEdit', 3);
add_action('edit-advanced-options', 'bookmark_AddEdit', 3);

function bookmark_AddEdit(){
global $id, $lang;

	$xfields = new XfieldsData();

return '<fieldset><legend>'.$lang['bookmark'].'</legend><label for="bookmark"><input type="checkbox" id="bookmark" name="bookmark" value="on"'.($xfields->fetch($id, 'bookmark') == 'on' ? ' checked="checked"' : '').'>&nbsp;'.$lang['addTo'].'</label></fieldset>';
}

// ���������� ���������
add_action('new-save-entry', 'add2bookmark');
add_action('edit-save-entry', 'add2bookmark');

function add2bookmark(){
global $id;

	// ��������� ���������
	$xfields = new XfieldsData();

	if ($_POST['bookmark']){ // ���� $_POST['bookmark'] �� ������ - ����������
		$xfields->set($_POST['bookmark'], $id, 'bookmark');
	} else { // ���� ������, �� ������� �����
		$xfields->deletefield($id, 'bookmark');
	}

	$xfields->save();
}

// ��� ����� ������ �� ��������, � �������� � ��������� ����
?>