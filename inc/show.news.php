<?php
$where = array();
$where = run_filters('also-allow', $where);

if ($allow_full_story or $allow_add_comment){
	$post = 'full';

	if ($title){
		$where[] = "url = $title";
	} elseif ($time){
		$where[] = "date = $time";
	} elseif ($id){
		$where[] = "id = $id";
	}
} else {
	$post = 'short';

	if (!$is_logged_in or $is_logged_in and $member['level'] == 4){
		$where[] = 'hidden = 0';
		$where[] = 'and';
	}

	if ($user or $author){
		$where[] = 'author = '.($author ? $author : $user);
		$where[] = 'and';
	}

	if ($year and !$month){
		$where[] = 'date > '.@mktime(0, 0, 0, 1, 1, $year);
		$where[] = 'and';
		$where[] = 'date < '.@mktime(23, 59, 59, ($year >= date("Y") ? date("n") : 12), ($year >= date("Y") ? date("d") : 31), $year);
	} elseif ($year and $month and !$day){
		$where[] = 'date > '.@mktime(0, 0, 0, $month, 1, $year);
		$where[] = 'and';
		$where[] = 'date < '.@mktime(23, 59, 59, $month, (($year >= date("Y") and $month >= date("n")) ? date("d") : 31), $year);
	} elseif ($year and $month and $day){
		if($year >= date("Y") and $month >= date("n") and $day >= date("d")){
			$where[] = 'hidden = 2';
		}
		else{
			$where[] = 'date > '.@mktime(0, 0, 0, $month, $day, $year);
			$where[] = 'and';
			$where[] = 'date < '.@mktime(23, 59, 59, $month, $day, $year);
		}
	} else {
		$where[] = 'date < '.(time() + $config_date_adjust * 60);
	}

	/*if (reset($allow_categories)){
		$where[] = 'and';
		$where[] = 'category =~ %'.(count($allow_categories) > 1 ? '['.join('|', $allow_categories).']' : join('', $allow_categories)).'%';

		foreach ($allow_categories as $k => $v){
			for ($i = 0; $i < 10; $i++){
				if (!in_array($v.$i, $allow_categories)){
					$where[] = 'and';
					$where[] = 'category !~ %'.$v.$i.'%';
				}

				if (!in_array($i.$v, $allow_categories)){
					$where[] = 'and';
					$where[] = 'category !~ %'.$i.$v.'%';
				}
			}
		}
	}*/
	
	
	if ($allow_categories[0] != ''){
		foreach ($allow_categories as $k => $v){
			for ($i = 0; $i < 10; $i++){
				if (!in_array($v.$i, $allow_categories)){
					$where[] = 'and';
					$where[] = 'category !~ %'.$v.$i.'%';
				}

				if (!in_array($i.$v, $allow_categories)){
					$where[] = 'and';
					$where[] = 'category !~ %'.$i.$v.'%';
				}
			}
		}
		
		$where[] = 'and';
		foreach($allow_categories as $single_cat){
			$where[] = 'category =~ %'.$single_cat.'%';
			$where[] = 'or';
		}
		
		unset($where[count($where)-1]);
	}
}

$query = $sql->select(array(
		 'table'   => 'news',
		 'orderby' => $sort,
		 'join'	   => array('table' => 'story', 'where' => 'id = post_id'),
		 'where'   => $where,
		 'limit'   => array(($skip ? $skip : 0), $number)
		 ));

if (!$query and ($allow_full_story or $allow_add_comment)){
?>

<div class="error_message"><?=$echo['notFound']; ?></div>

<?
	$allow_comments	   = false;
	$allow_add_comment = false;
	return;
}

foreach ($query as $bg => $row){
	if ($bg%2 == 0){
		$alternating = 'cn_news_odd';
	} else {
		$alternating = 'cn_news_even';
	}

	if ($allow_full_story or $allow_add_comment){
		$id	   = $_GET['id']	= $_POST['id']	  = $row['id'];
		$ucat  = $_GET['ucat']	= $_POST['ucat']  = reset($ucat = explode(',', $row['category']));
		$title = $_GET['title'] = $_POST['title'] = $row['title'];
		$link  = $_GET['link']	= $_POST['link']  = cute_get_link($row);
	}

	if ($is_logged_in and ($member['level'] < 3 or ($member['level'] == 3 and $news['author'] == $member['username']))){
		$allow_edit_comment = true;
	}

	if ($config_date_header == 'yes' and $dateheader_S != langdate('dmY', $row['date']) and $allow_active_news){
		$dateheader_S = langdate('dmY', $row['date']);
		$dateheader	  = langdate($config_date_headerformat, $row['date']);
		$dateheader_p = $template_dateheader;
		$dateheader_p = str_replace('{dateheader}', $dateheader, $dateheader_p);
		$dateheader_p = str_replace('[date-link]', '<a href="'.cute_get_link($row, 'day').'">', $dateheader_p);
		$dateheader_p = str_replace('[/date-link]', '</a>', $dateheader_p);

		echo $dateheader_p;
	}

	if (!$cache = cute_cache($row['id'], $cache_uniq, $post.($page > 1 ? '.'.$page : ''))){
		if ($allow_full_story or $allow_add_comment){
			$output		 = $template_full;
			$output		 = run_filters('news-show-generic', $output);
			$row['full'] = explode('<!--nextpage-->', ($row['full'] ? $row['full'] : $row['short']));
			$page_count	 = sizeof($row['full']);
			$row['full'] = $row['full'][($page ? ($page - 1) : 0)];

			if ($page_count > 1){
				$output = str_replace('[page-link]', '', $output);
				$output = str_replace('[/page-link]', '', $output);

				for ($i = 1; $i < $page_count + 1; $i++){
					if (($page and $page == $i) or ($allow_full_story and !$page and $i == 1)){
						$pages .= ' <b>'.$i.'</b> ';
					} else {
						$pages .= ' <a href="{link=home/post}?page='.$i.'">'.$i.'</a> ';
					}
				}
			}
		} else {
			$output = $template_active;
			$output = run_filters('news-show-generic', $output);
		}

		if ($cat_arr = explode(',', $row['category'])){
			$cat = array();

			foreach ($cat_arr as $v){
				$cat['id'][]   = $v;
				$cat['name'][] = ($cat_name[$v] ? '<a href="'.cute_get_link(array('id' => $v, 'url' => $cat_url[$v]), 'category').'">'.$cat_name[$v].'</a>' : '');
				$cat['icon'][] = ($cat_icon[$v] ? '<a href="'.cute_get_link(array('id' => $v, 'url' => $cat_url[$v]), 'category').'"><img src="'.$cat_icon[$v].'" alt="" border="0" align="absmiddle"></a>' : '');
			}
		}

		if ($row['full'] and !$allow_full_story){
			$output = str_replace('[full-link]', '', $output);
			$output = str_replace('[/full-link]', '', $output);
		}

		if (!reset($cat['name'])){
			$output = preg_replace('/\[catheader\](.*?)\[\/catheader\]/i', '', $output);
		}

		$output = str_replace('{id}', $row['id'], $output);
		$output = str_replace('{title}', run_filters('news-entry-content', $row['title']), $output);
		$output = str_replace('{date}', langdate($config_timestamp_active, $row['date']), $output);
		$output = str_replace('{author}', $user_name[$row['author']], $output);
		$output = str_replace('{avatar}', ($row['avatar'] ? '<img src="'.$row['avatar'].'" alt="" border="0" align="absmiddle">' : ''), $output);
		$output = str_replace('[catheader]', '', $output);
		$output = str_replace('[/catheader]', '', $output);
		$output = str_replace('{category}', join(', ', $cat['name']), $output);
		$output = str_replace('{category-icon}', join(', ', $cat['icon']), $output);
		$output = str_replace('{category-id}', join(', ', $cat['id']), $output);
		$output = str_replace('{short-story}', run_filters('news-entry-content', $row['short']), $output);
		$output = str_replace('{short-story-length}', strlen($row['short']), $output);
		$output = str_replace('{full-story}', run_filters('news-entry-content', wordwrap($row['full'], 150)), $output);
		$output = str_replace('{full-story-length}', strlen($row['full']), $output);
		$output = str_replace('{avatar-url}', $row['avatar'], $output);
		$output = str_replace('{pages}', $pages, $output);
		$output = str_replace('{php-self}', $PHP_SELF, $output);
		$output = str_replace('{cutepath}', $config_http_script_dir, $output);
		$output = str_replace('{imagepath}', $config_path_image_upload, $output);
		$output = str_replace('{alternating}', $alternating, $output);
		$output = run_filters('news-entry', $output);
		$output = preg_replace('/{link=(.*?)}/ie', "cute_get_link('\\1')", $output);
		$output = preg_replace('/{date=(.*?)}/ie', "langdate('\\1', $row[date])", $output);
		$output = preg_replace('/\[page-link\](.*?)\[\/page-link\]/is', '', $output);
		$output = preg_replace('/\[full-link\](.*?)\[\/full-link\]/is', '', $output);
		$output = replace_news('show', $output);
		$cache	= $output;
		file_write($cache_file, $cache);
	}

	if ($allow_full_story and $_POST['action'] != 'addcomment' and !$page){
		$sql->update(array(
		'table'	 => 'news',
		'where'	 => array("id = $row[id]"),
		'values' => array('views' => $row['views'] + 1)
		));
	}

	$cache = run_filters('news-entry-dynamic', $cache);
	$cache = str_replace('{views}', $row['views'], $cache);
	$cache = str_replace('{comments}', $row['comments'], $cache);

	if (!global_cache){
		if ($is_logged_in and ($member['level'] < 3 or $member['username'] == $row['author'])){
			$cache = str_replace('[if-logged]', '', $cache);
			$cache = str_replace('[/if-logged]', '', $cache);
			$cache = preg_replace('/\[not-logged\](.*?)\[\/not-logged\]/is', '', $cache);
		} else {
			$cache = str_replace('[not-logged]', '', $cache);
			$cache = str_replace('[/not-logged]', '', $cache);
			$cache = preg_replace('/\[if-logged\](.*?)\[\/if-logged\]/is', '', $cache);
		}
	}

	echo $cache;
}

// << Previous & Next >>
//$PHP_SELF	   = @preg_replace($skip.'$', '', $_SERVER['REQUEST_URI']);
$prev_next_msg = $template_prev_next;

//----------------------------------
// Previous link
//----------------------------------
if ($skip){
	$prev_next_msg = preg_replace('/\[prev-link\](.*?)\[\/prev-link\]/si', '<a href="'.$PHP_SELF.(($skip - $number) > 0 ? '?skip='.($skip-$number) : '').'">\\1</a>', $prev_next_msg);
	$prev_next_msg = preg_replace('/\[first-link\](.*?)\[\/first-link\]/si', '<a href="'.$PHP_SELF.'?skip=0">\\1</a>', $prev_next_msg);
} else {
	$prev_next_msg = preg_replace('/\[prev-link\](.*?)\[\/prev-link\]/si', '', $prev_next_msg);
	$prev_next_msg = preg_replace('/\[first-link\](.*?)\[\/first-link\]/si', '', $prev_next_msg);
	$no_prev = true;
}

//----------------------------------
// Pages
//----------------------------------
if ($number){
	$count		   = sizeof($sql->select(array('table' => 'news', 'where' => $where)));
	$count		   = ($allow_full_story ? 0 : $count);
	$pages_count   = @ceil($count / $number);
	$pages_skip	   = 0;
	$pages		   = '';
	$pages_section = 3;

	if ($pages_count > 10){
		for ($j = 1; $j <= $pages_section; $j++){
			if ($pages_skip != $skip){
				$pages .= '<a href="'.$PHP_SELF.($pages_skip > 0 ? '?skip='.$pages_skip : '').'">'.$j.'</a> ';
			} else {
				$pages .= ' <b>'.$j.'</b> ';
			}

			$pages_skip += $number;
		}

		if (((($skip / $number) + 1) > 1) and ((($skip / $number) + 1) < $pages_count)){
			$pages .= ((($skip / $number) + 1) > ($pages_section + 2)) ? '... ' : ' ';
			$page_min = ((($skip / $number) + 1) > ($pages_section + 1)) ? ($skip / $number) : ($pages_section + 1);
			$page_max = ((($skip / $number) + 1) < ($pages_count - ($pages_section + 1))) ? (($skip / $number) + 1) : $pages_count - ($pages_section + 1);

			$pages_skip = ($page_min - 1) * $number;

			for ($j = $page_min; $j < $page_max + ($pages_section - 1); $j++){
				if ($pages_skip != $skip){
					$pages .= '<a href="'.$PHP_SELF.($pages_skip > 0 ? '?skip='.$pages_skip : '').'">'.$j.'</a> ';
				} else {
					$pages .= ' <b>'.$j.'</b> ';
				}

				$pages_skip += $number;
			}

			$pages .= ((($skip / $number) + 1) < $pages_count - ($pages_section + 1)) ? '... ' : ' ';
		} else {
			$pages .= '... ';
		}

		$pages_skip = ($pages_count - $pages_section) * $number;

		for ($j = ($pages_count - ($pages_section - 1)); $j <= $pages_count; $j++){
			if ($pages_skip != $skip){
				$pages .= '<a href="'.$PHP_SELF.($pages_skip > 0 ? '?skip='.$pages_skip : '').'">'.$j.'</a> ';
			} else {
				$pages .= ' <b>'.$j.'</b> ';
			}

			$pages_skip += $number;
		}
	}
	else {
		 for ($j = 1; $j <= $pages_count; $j++){
			if ($pages_skip != $skip){
				$pages .= '<a href="'.$PHP_SELF.($pages_skip > 0 ? '?skip='.$pages_skip : '').'">'.$j.'</a> ';
			} else {
				$pages .= ' <b>'.$j.'</b> ';
			}

			$pages_skip += $number;
		}
	}

	$prev_next_msg = str_replace('{pages}', $pages, $prev_next_msg);
	$prev_next_msg = str_replace('{current-page}', ($skip + $number) / $number, $prev_next_msg);
	$prev_next_msg = str_replace('{total-pages}', $pages_count, $prev_next_msg);
}

//----------------------------------
// Next link
//----------------------------------
if ($skip + $number < $count){
	$prev_next_msg = preg_replace('/\[next-link\](.*?)\[\/next-link\]/si', '<a href="'.$PHP_SELF.'?skip='.($skip + $number).'">\\1</a>', $prev_next_msg);
	$prev_next_msg = preg_replace('/\[last-link\](.*?)\[\/last-link\]/si', '<a href="'.$PHP_SELF.'?skip='.(($pages_count - 1) * $number).'">\\1</a>', $prev_next_msg);
} else {
	$prev_next_msg = preg_replace('/\[next-link\](.*?)\[\/next-link\]/si', '', $prev_next_msg);
	$prev_next_msg = preg_replace('/\[last-link\](.*?)\[\/last-link\]/si', '', $prev_next_msg);
	$no_next = true;
}

$prev_next_msg = run_filters('prev-next-msg', $prev_next_msg);

if (!$no_prev or !$no_next){
	echo $prev_next_msg;
}
?>