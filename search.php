<?
include_once 'head.php';

$sday[] = '';

for ($i = 1; $i < 32; $i++){
	$sday[$i] = $i;
}

$smonth[] = '';

for ($i = 1; $i < 13; $i++){
	$smonth[$i] = $i;
}

$syear[] = '';

for ($i = 1999; $i < (date('Y') + 1); $i++){
	$syear[$i] = $i;
}

function search_this_cat($id){
global $category;

return ($id == $category ? ' selected' : '');
}
?>

<!-- КОД ПОИСКА / НАЧАЛО / МОЖНО НАЧАТЬ ВЫДЕЛЕНИЕ ДЛЯ КОПИРОВАНИЯ -->
<form method="get" action="<?=$_SERVER['PHP_SELF']; ?>">
<input name="do" type="hidden" value="search">
<table width="400" border="0" cellspacing="0" cellpadding="0">
 <tr>
  <td width="1">Search
  <td width="99%"><input type="text" name="search" size="20" value="<?=$search; ?>">
 <tr>
  <td>In category
  <td><select size="1" name="category"><option value="">All</option><?=category_get_tree('&nbsp;', '<option value="{id}"[php]search_this_cat({id})[/php]>{prefix}{name}</option>'); ?></select>
 <tr>
  <td><nobr>Date (year/month/date)</nobr>&nbsp;
  <td><?=makeDropDown($syear, 'year', $year); ?>/<?=makeDropDown($smonth, 'month', $month); ?>/<?=makeDropDown($sday, 'day', $day); ?>
 <tr>
  <td colspan="2"><input type="submit" value=" Search ">
</table>
</form>
<!-- КОД ПОИСКА / КОНЕЦ / МОЖНО ЗАКОНЧИТЬ ВЫДЕЛЕНИЕ И КОПИРОВАТЬ -->

<?
add_filter('also-allow', 'search');

function search($where){
global $search, $sql;

	$search = strtolower($search);
	$search = htmlspecialchars($search);

	foreach ($sql->select(array('table' => 'story', 'where' => array("short =~ %$search%", 'or', "full =~ %$search%"))) as $row){
		$select[] = $row['post_id'];
	}

	foreach ($sql->select(array('table' => 'news', 'where' => array("title =~ %$search%"))) as $row){
		$select[] = $row['id'];
	}

    if ($select){
    	$where[] = 'id = '.(count($select) > 1 ? '['.join('|', $select).']' : join('', $select));
    	$where[] = 'and';
    } else {
    	$where = array('id = 0', 'and');
    }

return $where;
}


add_filter('news-entry-content', 'Highlight_Search', 999);

function Highlight_Search($output){
global $search;

	$output = formattext($search, $output);

return $output;
}

////////////////////////////////////////////////////////
// Function:    formattext
// Description: Выделение результата в найденом куске текста
// Original:    http://forum.dklab.ru/php/heap/AllocationOfResultInNaydenomAPieceOfTheText.html

function formattext($whatfind, $text){

	$pos    = @strpos(strtoupper($text), strtoupper($whatfind));
	$otstup = 200; // кол-во символов при выводе результата
	$result = '';

	if ($pos !== false){ //если найдена подстрока
	    if ($pos < $otstup){ //если встречается раньше чем первые N символов
	        $result = substr($text, 0, $otstup * 2); //то результат подстрока от начала и до N-го символа
	        $result = eregi_replace($whatfind, '<span class="hilite">'.$whatfind.'</span>', $result);
	    } else {
	        $start = $pos-$otstup;
	        //то результат N символов  от совпадения и N символов вперёд
	        $result = '...'.substr($text, $pos-$otstup, $otstup * 2).'...';
	        // выделяем
	        $result = eregi_replace($whatfind, '<span class="hilite">'.$whatfind.'</span>', $result);
	    }
	} else {
	    $result = substr($text, 0, $otstup * 2);
	}

return $result;
}

function category_selected($id){
global $category;

	if ($category == $id){
		return ' selected';
	}
}

if ($search){
	$template = 'Search';
	include $cutepath.'/show_news.php';
}
?>