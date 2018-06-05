<?php

$comments_per_page = 10;

if ($config_reverse_comments == 'yes'){
	$csort = array('date', 'DESC');
} else {
	$csort = array('date', 'ASC');
}

if (!$comments_per_page){
	$comments_per_page = $sql->table_count('comments');
}

$query = $sql->select(array(
		 'table'   => 'comments',
		 'where'   => array("post_id = $id"),
		 'orderby' => $csort,
		 'limit'   => array(($cpage ? $cpage : 0), $comments_per_page)
		 ));

$count = count($sql->select(array('table' => 'comments', 'where' => array("post_id = $id"))));

if ($config_reverse_comments == 'yes'){
	$cjnumber = ($cpage ? (($count + 1) - $cpage) : $count + 1);
} else {
	$cjnumber = (($cpage and $cpage != 0) ? $cpage : 0);
}


foreach ($query as $bg => $row){
	if (!$cache = cute_cache($row['id'], $cache_uniq, 'comment')){
		if ($bg%2 == 0){
			$alternating = 'cn_comment_odd';
		} else {
			$alternating = 'cn_comment_even';
		}

		if ($config_reverse_comments == 'yes'){
			$cjnumber--;
		} else {
			$cjnumber++;
		}

		if ($row['mail'] != 'none' and !$user_name[$row['author']]){
			if (preg_match("/^[\.A-z0-9_\-]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/", $row['mail'])){
				$mail	= explode('@', $row['mail']);
				$output = str_replace('{author}',
'<script>
document.write("<a href=mailto:"); document.write("'.$mail[0].'"+"%40"); document.write("'.$mail[1].'>'.$row['author'].'</a>");
</script>
<noscript>
<a href="mailto:'.str_replace('@', ' at ', str_replace('.', ' dot ', $row['mail'])).'">'.$row['author'].'</a>
</noscript>'


				, $template_comment);
			} else {
				$output = str_replace('{author}', '<a href="'.((substr($row['mail'], 0, 3) == 'www') ? 'http://' : '').$row['mail'].'">'.$row['author'].'</a>', $template_comment);
			}
		} else {
			$output = str_replace('{author}', ($user_name[$row['author']] ? $user_name[$row['author']] : $row['author']), $template_comment);
		}

		if ($row['reply']){
			$output = str_replace('{answer}', run_filters('news-comment-content', $row['reply']), $output);
			$output = str_replace('[answer]', '', $output);
			$output = str_replace('[/answer]', '', $output);
		} else {
			$output = preg_replace('/\[answer\](.*?)\[\/answer\]/is', '', $output);
		}

		$output = str_replace('{id}', $id, $output);
		$output = str_replace('{title}', $title, $output);
		$output = str_replace('{link}', $link, $output);
		$output = str_replace('{mail}', $row['mail'], $output);
		$output = str_replace('{avatar}', $user_avatar[$row['author']], $output);
		$output = str_replace('{date}', langdate($config_timestamp_comment, $row['date']), $output);
		$output = preg_replace('/{date=(.*?)}/ie', "date('\\1', $row[date])", $output);
		$output = str_replace('{comment-id}', $row['id'], $output);
		$output = str_replace('{comment}', run_filters('news-comment-content', $row['comment']), $output);
		$output = str_replace('{cutepath}', $config_http_script_dir, $output);
		$output = str_replace('{alternating}', $alternating, $output);
		$output = str_replace('{comnum}', $cjnumber, $output);
		$output = run_filters('news-comment', $output);
		$output = replace_comment('show', $output);
		$cache	= $output;
		file_write($cache_file, $cache);
	}

	if (!global_cache){
		if ($allow_edit_comment or ($config_allow_edit_comments == 'yes' and $member['username'] == $row['author'])){
			$cache = str_replace('[if-logged]', '', $cache);
			$cache = str_replace('[/if-logged]', '', $cache);
			$cache = preg_replace('/\[not-logged\](.*?)\[\/not-logged\]/si', '', $cache);
		} else {
			$cache = str_replace('[not-logged]', '', $cache);
			$cache = str_replace('[/not-logged]', '', $cache);
			$cache = preg_replace('/\[if-logged\](.*?)\[\/if-logged\]/si', '', $cache);
		}
	}

	echo $cache;
}

// << Previous & Next >>
$cprev_next_msg = $template_cprev_next;

//----------------------------------
// Previous link
//----------------------------------
if ($cpage){
	$cprev_next_msg = preg_replace("'\[prev-link\](.*?)\[/prev-link\]'si", '<a href="?cpage='.($cpage - $comments_per_page).'">\\1</a>', $cprev_next_msg);
} else {
	$cprev_next_msg = preg_replace("'\[prev-link\](.*?)\[/prev-link\]'si", "", $cprev_next_msg);
	$no_cprev = true;
}

//----------------------------------
// Pages
//----------------------------------
if ($comments_per_page){
	$pages_count   = @ceil($count / $comments_per_page);
	$pages_cpage   = 0;
	$pages		   = array();
	$pages_section = 3;
	$pages_break   = 4;

	if ($pages_break and $pages_count > $pages_break){
		for ($j = 1; $j <= $pages_section; $j++){
			if ($pages_cpage != $cpage){
				$pages[] = '<a href="?cpage='.$pages_cpage.'">'.$j.'</a>';
			} else {
				$pages[] = '<b>'.$j.'</b>';
			}

			$pages_cpage += $comments_per_page;
		}

		if (((($cpage / $comments_per_page) + 1) > 1) and ((($cpage / $comments_per_page) + 1) < $pages_count)){
			$pages[] = ((($cpage / $comments_per_page) + 1) > ($pages_section + 2)) ? '...' : '';
			$page_min = ((($cpage / $comments_per_page) + 1) > ($pages_section + 1)) ? ($cpage / $comments_per_page) : ($pages_section + 1);
			$page_max = ((($cpage / $comments_per_page) + 1) < ($pages_count - ($pages_section + 1))) ? (($cpage / $comments_per_page) + 1) : $pages_count - ($pages_section + 1);

			$pages_cpage = ($page_min - 1) * $comments_per_page;

			for ($j = $page_min; $j < $page_max + ($pages_section - 1); $j++){
				if ($pages_cpage != $cpage){
					$pages[] = '<a href="?cpage='.($pages_cpage).'">'.$j.'</a>';
				} else {
					$pages[] = '<b>'.$j.'</b>';
				}

				$pages_cpage += $comments_per_page;
			}

			$pages[] = ((($cpage / $comments_per_page) + 1) < $pages_count - ($pages_section + 1)) ? '...' : '';
		} else {
			$pages[] = '...';
		}

		$pages_cpage = ($pages_count - $pages_section) * $comments_per_page;

		for ($j = ($pages_count - ($pages_section - 1)); $j <= $pages_count; $j++){
			if ($pages_cpage != $cpage){
				$pages[] = '<a href="?cpage='.($pages_cpage).'">'.$j.'</a>';
			} else {
				$pages[] = '<b>'.$j.'</b>';
			}

			$pages_cpage += $comments_per_page;
		}
	} else {
		 for ($j = 1; $j <= $pages_count; $j++){
			if ($pages_cpage != $cpage){
				$pages[] = '<a href="?cpage='.$pages_cpage.'">'.$j.'</a>';
			} else {
				$pages[] = ' <b>'.$j.'</b> ';
			}

			$pages_cpage += $comments_per_page;
		}
	}

	$cprev_next_msg = str_replace("{cpages}", join(' ', $pages), $cprev_next_msg);
	$cprev_next_msg = str_replace("{current-cpage}", (($cpage + $comments_per_page) / $comments_per_page), $cprev_next_msg);
	$cprev_next_msg = str_replace("{total-cpages}", $pages_count, $cprev_next_msg);
}

//----------------------------------
// Next link
//----------------------------------
if ($cpage + $comments_per_page < $count){
	$cprev_next_msg = preg_replace("'\[next-link\](.*?)\[/next-link\]'si", '<a href="?cpage='.($cpage + $comments_per_page).'">\\1</a>', $cprev_next_msg);
} else {
	$cprev_next_msg = preg_replace("'\[next-link\](.*?)\[/next-link\]'si", "", $cprev_next_msg);
	$no_cnext = true;
}

$cprev_next_msg = run_filters('cprev-next-msg', $cprev_next_msg);

if (!$no_cprev or !$no_cnext)
	echo $cprev_next_msg;
?>