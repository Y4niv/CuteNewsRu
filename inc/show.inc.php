<?php
do {
	$echo				= cute_lang('shows');
	$allow_add_comment	= run_filters('news-allow-addcomment', $allow_add_comment);
	$allow_comments		= run_filters('news-show-comments', $allow_comments);
	$allow_comment_form = run_filters('news-allow-commentform', $allow_comment_form);

	if ($allow_active_news or $allow_full_story){
		include $cutepath.'/inc/show.news.php';
	}

	if ($allow_add_comment){
		include $cutepath.'/inc/show.addcomment.php';
	}

	if ($allow_comments){
		include $cutepath.'/inc/show.comments.php';
	}

	if ($allow_comments and $allow_comment_form){
		include $cutepath.'/inc/show.commentform.php';
	}
} while (0);
?>