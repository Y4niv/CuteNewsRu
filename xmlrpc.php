<?php
include_once 'head.php';
include_once $cutepath.'/inc/xmlrpc.inc.php';

function XMLRPC_symbol_decoder($str){return totranslit($str);}

$xmlrpc_request = XMLRPC_parse($HTTP_RAW_POST_DATA);
$methodName 	= XMLRPC_getMethodName($xmlrpc_request);
$params 		= XMLRPC_getParams($xmlrpc_request);

$methods = array(
	'blogger.getUsersBlogs'     => 'blogger_getUsersBlogs',

	'metaWeblog.getPost'        => 'metaWeblog_getPost',
	'metaWeblog.getRecentPosts' => 'metaWeblog_getRecentPosts',
	'metaWeblog.getCategories'  => 'metaWeblog_getCategories',

	'metaWeblog.newPost'        => 'metaWeblog_newPost',
	'metaWeblog.editPost'       => 'metaWeblog_editPost',
	'blogger.deletePost'        => 'metaWeblog_deletePost',
	'metaWeblog.deletePost'     => 'metaWeblog_deletePost',

	'demo.SayHello'				=> 'demo_SayHello',

	'method_not_found'          => 'method_not_found'
);

function demo_SayHello($params){
	XMLRPC_response('Hello Dolly ;).');
}

function blogger_getUsersBlogs($params){
global $config_http_home_url, $config_home_title;

	if (!check_login($params[1], md5x($params[2]))){
		XMLRPC_error(666, 'Неверный логин или пароль!');
		exit;
	}

	XMLRPC_response(array(
		XMLRPC_prepare(array(array(
		'url'      => $config_http_home_url,
		'blogName' => $config_home_title,
		'blogid'   => '1'
		))
	)));
}

function metaWeblog_getPost($params){
global $sql;

	if (!check_login($params[1], md5x($params[2]))){
		XMLRPC_error(666, 'Неверный логин или пароль!');
		exit;
	}

    $query = $sql->select(array(
    	     'table'   => 'news',
    	     'orderby' => array('id', 'DESC'),
    	     'join'    => array('table' => 'story', 'where' => 'id = post_id'),
    	     ));

	foreach($query as $row){
        $post['postid']      = $row['id'];
		$post['dateCreated'] = XMLRPC_convert_timestamp_to_iso8601($row['date']);
        $post['title'] 		 = $row['title'];
		$post['description'] = replace_news('admin', $row['short']);
		$posts[]             = $post;
	}

	XMLRPC_response(XMLRPC_prepare($posts), WEBLOG_XMLRPC_USERAGENT);
}

function metaWeblog_editPost($params){
global $sql;

	if (!check_login($params[1], md5x($params[2]))){
		XMLRPC_error(666, 'Неверный логин или пароль!');
		exit;
	}

	if (!$params[3]['title']){
		XMLRPC_error(666, 'Заголовок не может быть пустым!!');
		exit;
	}

    foreach ($sql->select(array('table' => 'categories')) as $row){
        $cat_name[XMLRPC_symbol_decoder($row['name'])] = $row['id'];
    }

    if ($params[3]['categories']){
	    foreach ($params[3]['categories'] as $category){
	        $categories[] = $cat_name[XMLRPC_symbol_decoder($category)];
	    }
    }

	$sql->update(array(
	'table'  => 'news',
	'where'  => array("id = $params[0]"),
	'values' => array(
	         'title'    => replace_news('add', $params[3]['title']),
	         'short'    => strlen($params[3]['description']),
	         'category' => @join(',', $categories),
	         'url'      => totranslit($params[3]['title'])
	         )
	));

	$sql->update(array(
    'table'  => 'story',
    'where'  => array("post_id = $params[0]"),
    'values' => array('short' => replace_news('add', $params[3]['description']))
    ));

	XMLRPC_response(XMLRPC_prepare(true), WEBLOG_XMLRPC_USERAGENT);
}

function metaWeblog_deletePost($params){
global $sql;

	if (!check_login($params[2], md5x($params[3]))){
		XMLRPC_error(666, 'Неверный логин или пароль!');
		exit();
	}

    $sql->delete(array(
	'table' => 'news',
	'where' => array("id = $params[1]")
	));

    $sql->delete(array(
    'table' => 'story',
    'where' => array("post_id = $params[1]")
    ));

    $sql->delete(array(
    'table' => 'comments',
    'where' => array("post_id = $params[1]")
    ));

	XMLRPC_response(XMLRPC_prepare(true), WEBLOG_XMLRPC_USERAGENT);
}

function metaWeblog_getRecentPosts($params){
global $sql;

	if (!check_login($params[1], md5x($params[2]))){
		XMLRPC_error(666, 'Неверный логин или пароль!');
		exit;
	}

    foreach ($sql->select(array('table' => 'categories')) as $row){
        $cat_name[$row['id']] = XMLRPC_symbol_decoder($row['name']);
    }

    $query = $sql->select(array(
    	     'table'   => 'news',
    	     'orderby' => array('id', 'DESC'),
    	     'join'    => array('table' => 'story', 'where' => 'id = post_id'),
    	     'limit'   => array(0, $params[3])
    	     ));

	foreach($query as $row){
        $post['postid']      = $row['id'];
		$post['dateCreated'] = XMLRPC_convert_timestamp_to_iso8601($row['date']);
        $post['title'] 		 = $row['title'];
        $post['link'] 		 = cute_get_link($row);
        $post['permaLink']   = cute_get_link($row);
		$post['description'] = replace_news('admin', $row['short']);
		$post['categories']  = array();

		foreach (explode(',', $row['category']) as $category){
			$post['categories'][] = $cat_name[$category];
		}

		$posts[] = $post;
	}

	XMLRPC_response(XMLRPC_prepare($posts), WEBLOG_XMLRPC_USERAGENT);
}

function metaWeblog_newPost($params){
global $sql, $config_date_adjust, $config_approve_news;

	if (!check_login($params[1], md5x($params[2]))){
		XMLRPC_error(666, 'Неверный логин или пароль!');
		exit;
	}
	if (!$params[3]['title']){
		XMLRPC_error(666, 'Заголовок не может быть пустым!');
		exit;
	}

    foreach ($sql->select(array('table' => 'categories')) as $row){
        $cat_name[XMLRPC_symbol_decoder($row['name'])] = $row['id'];
    }

    if ($params[3]['categories']){
	    foreach ($params[3]['categories'] as $category){
	        $categories[] = $cat_name[XMLRPC_symbol_decoder($category)];
	    }
    }

	if (!$params[3]['dateCreated']){
		$params[3]['dateCreated'] = (time() + $config_date_adjust * 60);
	} else {
		$params[3]['dateCreated'] = ($params[3]['dateCreated'] + $config_date_adjust * 60);
	}

	foreach ($sql->select(array('table' => 'users', 'where' => array("username = $params[1]"))) as $row){
		$member['level'] = $row['level'];
	}

	$sql->insert(array(
	'table'  => 'news',
	'values' => array(
	            'date'     => $params[3]['dateCreated'],
	            'author'   => $params[1],
	            'title'    => replace_news('add', $params[3]['title']),
	            'short'    => strlen($params[3]['description']),
	            'category' => @join(',', $categories),
	            'url'      => totranslit($params[3]['title']),
	            'hidden'   => (($config_approve_news == 'yes' and $member['level'] > 2) ? true : false)
	            )
	));

    $sql->insert(array(
	'table'  => 'story',
	'values' => array(
				'post_id' => $sql->last_insert_id('news', '', 'id'),
				'short'   => replace_news('add', $params[3]['description'])
				)
	));

	XMLRPC_response(XMLRPC_prepare($sql->last_insert_id('news', '', 'id')), WEBLOG_XMLRPC_USERAGENT);
}

function metaWeblog_getCategories($params){
global $sql;

	if (!check_login($params[1], md5x($params[2]))){
		XMLRPC_error(666, 'Неверный логин или пароль!');
		exit;
	}

    foreach ($sql->select(array('table' => 'categories')) as $row){
        $cat['categoryId']  = $row['id'];
        $cat['title']       = XMLRPC_symbol_decoder($row['name']);
        $cat['description'] = XMLRPC_symbol_decoder($row['name']);
        $cats[]             = $cat;
    }

	XMLRPC_response(XMLRPC_prepare($cats), WEBLOG_XMLRPC_USERAGENT);
}

function method_not_found($methodName){
	XMLRPC_error(666, 'Вызываемая процедура "'.$methodName.'" не существует!');
}

if (!$methods[$methodName]){
	$methods['method_not_found']($methodName);
} else {
	$methods[$methodName]($params);
}
?>
