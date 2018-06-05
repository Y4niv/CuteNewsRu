<?php
/*
Plugin Name:	CN functions
Plugin URI:		http://cutenews.ru
Description:	<code>&lt;?=cn_calendar(); ?&gt;</code> - calendar.<br /><code>&lt;?=cn_archives(); ?&gt;</code> - months list.<br /><code>&lt;?=cn_category(); ?&gt;</code> - categories list.<br /><code>&lt;?=cn_title(); ?&gt;</code> - titles.
Version:		2.1
Application:	CuteNews.RU
Author:			&#1051;&#1105;&#1093;&#1072; zloy &#1080; &#1082;&#1088;&#1072;&#1089;&#1080;&#1074;&#1099;&#1081;
Author URI:		http://lexa.cutenews.ru
*/

function cn_calendar(){
global $year, $month, $day, $PHP_SELF, $sql, $cache_file;

	if (!$cache = cute_cache('calendar')){
		foreach ($sql->select(array('table' => 'news', 'where' => array('date < '.time()))) as $row){
			$save[] = $row['date'];
		}

		@rsort($save);
		file_write($cache_file, @join("\r\n", $save));
	}

	$post_arr = file($cache_file);

	$year  = ($year ? $year : $_GET['year']);
	$month = ($month ? $month : $_GET['month']);
	$day   = ($day ? $day : $_GET['day']);

	if ($year and $month){
		$this['month'] = $month;
		$this['year']  = $year;
	} else {
		$this['month'] = date('m', $post_arr[0]);
		$this['year']  = date('Y', $post_arr[0]);
	}

	if (!$cache = cute_cache(($day ? $day.'.' : '').$this['month'].'.'.$this['year'])){
		foreach ($post_arr as $date){
			if ($this['year'] == date('Y', $date) and $this['month'] == date('m', $date)){
				$events[date('j', $date)] = $date;
			}

			if ($this['month'].$this['year'] != date('mY', $date)){
				$prev_next[] = $date;
			}
		}

		$cache = calendar($this['month'], $this['year'], $events, $prev_next);
		file_write($cache_file, $cache);
	}

return $cache;
}

function cn_archives($tpl = '<a href="{link}">{date} ({count})</a><br />', $sort = array('date', 'DESC')){
global $PHP_SELF, $sql, $cache_file, $config_date_adjust;
static $uniqid;

	if (!$cache = cute_cache('archives', $uniqid++)){
		foreach ($sql->select(array('table' => 'news', 'select' => array('date'), 'where' => array('date < '.(time() + $config_date_adjust * 60)), 'orderby' => $sort)) as $row){
			if ($arch != date('Y/m', $row['date'])){
				$arch	= date('Y/m', $row['date']);
				$find	= array('{date}', '{link}', '{count}');
				$repl	= array(lang(date('n', $row['date']), 'month').date(' Y', $row['date']), cute_get_link($row, 'month'), count_month_entry($arch));
				$cache .= str_replace($find, $repl, $tpl);
			}
		}

		file_write($cache_file, $cache);
   }

return $cache;
}

function cn_category($prefix = '&nbsp;', $tpl = '<a href="[php]cute_get_link($row, category)[/php]">{name} ([php]count_category_entry({id})[/php])</a><br />', $no_prefix = true, $level = 0){
global $PHP_SELF, $cache_file;
static $uniqid;

	if (!$cache = cute_cache('category', $uniqid++)){
		$cache = category_get_tree($prefix, $tpl, $no_prefix, $level);
		file_write($cache_file, $cache);
	}

return $cache;
}

function cn_title($separator = ' &raquo; ', $reverse = false, $type = 'title'){
global $sql, $_SERVER, $config_http_home_url, $config_home_title, $_GET, $cache_file;
static $uniqid;

	if (!$cache = cute_cache($type.'-'.str_replace(array('/', '?', '&', '='), '-', chicken_dick($_SERVER['REQUEST_URI'])), $uniqid++)){
		foreach ($_GET as $k => $v){
			$$k = @htmlspecialchars($v);
		}

		if (eregi('([0-9]{2}):([0-9]{2}):([0-9]{2})', $time)){
			$m_n  = array('jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'may' => 5, 'jun' => 6, 'jul' => 7, 'aug' => 8, 'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12);
			$time = explode(':', $time);
			$time = mktime($time[0], $time[1], $time[2], (eregi('[a-z]', $month) ? $m_n[$month] : $month), $day, $year);
		}

		if (eregi('[a-z]', $category)){
			$category = category_get_id($category);
		}

		$result[] = $config_home_title;

		if (!$id and !$title and !$time){
			if ($category){
				$category = explode($separator, category_get_title($category, $separator));
				$result[] = join($separator, ($reverse ? array_reverse($category) : $category));
			}

			if ($user or $author){
				$user	  = ($user ? $user : $author);
				$query	  = reset(
							$sql->select(array(
							'table'	  => 'users',
							'where'	  => array("username = $user", 'or', "id = $user")
							)));
				$result[] = ($query['name'] ? $query['name'] : $query['username']);
			}

			if ($year){
				$result[] = $year;
			}

			if ($month){
				$echo	 = cute_lang();
				$echo	 = $echo['calendar'];
				$f_num	 = array('01', '02', '03', '04', '05', '06', '07', '07', '09', '10', '11', '12');
				$f_name	 = array('jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec');
				$replace = array($echo['jan'], $echo['feb'], $echo['mar'], $echo['apr'], $echo['may'], $echo['jun'], $echo['jul'], $echo['aug'], $echo['sep'], $echo['oct'], $echo['nov'], $echo['dec']);
				$result[] = (eregi('[a-z]', $month) ? str_replace($f_name, $replace, $month) : str_replace($f_num, $replace, $month));
			}

			if ($day){
				$result[] = $day;
			}
		} else {
			if ($title){
				$where[] = "url = $title";
			} elseif ($time){
				$where[] = "date = $time";
			} elseif ($id){
				$where[] = "id = $id";
			}

			$query	  = reset(
						$sql->select(array(
						'table'	  => 'news',
						'where'	  => $where
						)));
			$result[] = replace_news('show', $query['title']);
		}

		$cache = join($separator, ($reverse ? array_reverse($result) : $result));
		file_write($cache_file, $cache);
	}

return $cache;
}

#-------------------------------------------------------------------------------

function count_month_entry($date){
global $sql;

	foreach ($sql->select(array('table' => 'news', 'select' => array('date'), 'where' => array('date < '.(time() + $config_date_adjust * 60)))) as $row){
		if ($date == date('Y/m', $row['date'])){
			$result += count($row);
		}
	}

return $result;
}

function count_category_entry($catid){
global $sql;

	foreach ($sql->select(array('table' => 'news', 'select' => array('category'))) as $row){
		$cat_arr = explode(',', $row['category']);
		foreach ($cat_arr as $cat){
			if ($catid == $cat){
				$result += count($row);
			}
		}
	}

return ($result ? $result : '0');
}

function calendar($cal_month, $cal_year, $events, $prev_next){
global $year, $month, $day, $PHP_SELF;

	$first_of_month	 = mktime(0, 0, 0, $cal_month, 7, $cal_year);
	$maxdays		 = date('t', $first_of_month) + 1; // 28-31
	$cal_day		 = 1;
	$weekday		 = date('w', $first_of_month); // 0-6

	if (is_array($prev_next)){
		sort($prev_next);

		foreach ($prev_next as $key => $value){
			if ($value < $first_of_month){
				$prev_of_month = $prev_next[$key];
			}
		}

		rsort($prev_next);

		foreach ($prev_next as $key => $value){
			if ($value > $first_of_month){
				$next_of_month = $prev_next[$key];
			}
		}
	}

	if ($prev_of_month){
		$tomonth['prev'] = '<a href="'.cute_get_link(array('date' => $prev_of_month), 'month').'" title="'.lang(date('n', $prev_of_month), 'month').date(' Y', $prev_of_month).'">&laquo;</a> ';
	}

	if ($next_of_month){
		$tomonth['next'] = ' <a href="'.cute_get_link(array('date' => $next_of_month), 'month').'" title="'.lang(date('n', $next_of_month), 'month').date(' Y', $next_of_month).'">&raquo;</a>';
	}

	$buffer = '<table id="calendar">
	<tr>
	 <td colspan="7" class="month">'.$tomonth['prev'].'<a href="'.cute_get_link(array('date' => $first_of_month), 'month').'" title="'.lang(date('n', $first_of_month), 'month').date(' Y', $first_of_month).'">'.lang(date('n', $first_of_month), 'month').' '.$cal_year.$tomonth['next'].'</a>
	<tr>
	 <th class="weekday">'.lang(1, 'weekday').'
	 <th class="weekday">'.lang(2, 'weekday').'
	 <th class="weekday">'.lang(3, 'weekday').'
	 <th class="weekday">'.lang(4, 'weekday').'
	 <th class="weekday">'.lang(5, 'weekday').'
	 <th class="weekend">'.lang(6, 'weekday').'
	 <th class="weekend">'.lang(7, 'weekday').'
	<tr>';

	if ($weekday > 0){
		$buffer .= '<td colspan="'.$weekday.'">&nbsp;';
	}

	while ($maxdays > $cal_day){
		if ($weekday == 7){
			$buffer .= '<tr>';
			$weekday = 0;
		}

		# В данный день есть новость
		if ($events[$cal_day]){
			$date['title'] = langdate('l, d M Y', $events[$cal_day]);
			$link = cute_get_link(array('date' => $events[$cal_day]), 'day');

			if ($weekday == '5' or $weekday == '6'){ // Если суббота и воскресенье. Слава КПСС!!!
				if ($day == $cal_day){
					$buffer .= '<td class="weekend"><a href="'.$link.'" title="'.$date['title'].'"><b>'.$cal_day.'</b></a>';
				} else {
					$buffer .= '<td class="endday"><a href="'.$link.'" title="'.$date['title'].'">'.$cal_day.'</a>';
				}
			} else { // Рабочии дни. Вперёд, стахановцы!!!
				if ($day == $cal_day){ // активный
					$buffer .= '<td class="weekday"><a href="'.$link.'" title="'.$date['title'].'"><b>'.$cal_day.'</b></a>';
				} else {  // пассивный, дурашка
					$buffer .= '<td class="day"><a href="'.$link.'" title="'.$date['title'].'">'.$cal_day.'</a>';
				}
			}
		} else { // В данный день новостей нет. Хуйовый день...
			if ($weekday == '5' or $weekday == '6'){ // дни, когда по телеку нихуя нет :(
				$buffer .= '<td class="endday">'.$cal_day;
			} else { // работяги хлещат водку после труда
				$buffer .= '<td class="day">'.$cal_day;
			}
		}

		$cal_day++;
		$weekday++;
	}

	if ($weekday != 7){
		$buffer .= '<td colspan="'.(7 - $weekday).'">&nbsp;';
	}

return $buffer.'</table>';
}

function lang($num, $set){
global $echo;

	$lang = array('month' => array($echo['calendar']['jan'], $echo['calendar']['feb'], $echo['calendar']['mar'], $echo['calendar']['apr'], $echo['calendar']['may'], $echo['calendar']['jun'], $echo['calendar']['jul'], $echo['calendar']['aug'], $echo['calendar']['sep'], $echo['calendar']['oct'], $echo['calendar']['nov'], $echo['calendar']['dec']), 'weekday' => array($echo['calendar']['mon'], $echo['calendar']['tue'], $echo['calendar']['wed'], $echo['calendar']['thu'], $echo['calendar']['fri'], $echo['calendar']['sat'], $echo['calendar']['sun']));

return $lang[$set][($num - 1)];
}
?>