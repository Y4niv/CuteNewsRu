<?php
$array = array (
  'Custom_Quick_Tags' =>
  array (
	'tags' =>
	array (
	  0 =>
	  array (
		'name' => 'Bold',
		'tag' => 'b',
		'complex' => '',
		'replace' => '<strong>$1</strong>',
	  ),
	  1 =>
	  array (
		'name' => 'Italic',
		'tag' => 'i',
		'complex' => '',
		'replace' => '<em>$1</em>',
	  ),
	  2 =>
	  array (
		'name' => 'Underline',
		'tag' => 'u',
		'complex' => '',
		'replace' => '<u>$1</u>',
	  ),
	  3 =>
	  array (
		'name' => 'Linethrough',
		'tag' => 'del',
		'complex' => '',
		'replace' => '<del>$1</del>',
	  ),
	  4 =>
	  array (
		'name' => 'Quote',
		'tag' => 'q',
		'complex' => '',
		'replace' => '<blockquote><b>&laquo;</b><small>$1</small><b>&raquo;</b></blockquote>',
	  ),
	),
  ),
  'CommSpy' =>
  array (
	'subj' => 'На сайте {page-title} новый комментарий от {author}',
	'body' => 'Здравствуйте.{nl}{nl}Вы подписались на получение новых комментариев с сайта {page-title}. Кто-то оставил там новый комментарий.{nl}{nl}{nl}Комментарий:{nl}------------{nl}{comment}{nl}{nl}{nl}URL: {link}',
  ),
  'CN2LJ' =>
  array (
	'title' => '{title}',
	'body' => '{story}{nl}{nl}<p style="text-align: right;"><a href="{link}" style="color: #666;font-size: 9px;" title="&laquo;{title}&raquo;">{hometitle}</a>',
	'story' => '{story}{nl}{nl}<p style="text-align: right;"><a href="{link}" style="color: #666;font-size: 9px;" title="&laquo;{title}&raquo;">{hometitle}</a>',
  ),
);
?>
