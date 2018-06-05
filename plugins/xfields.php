<?php
/*
Plugin Name: 	XFields
Description: 	Add custom input fields.
Version: 		1.0
Application: 	CuteNews
Author: 		SMKiller2
*/

add_filter('template-variables-active', 'xfields_templates');
add_filter('template-variables-full', 'xfields_templates');

function xfields_templates($template){

	$template['[xfvalue_X]'] = 'XField with the same name as provided in the tag as "X"';
	$template['[xfgiven_X] and [/xfgiven_X]'] = 'Shows XField with the name "X" only if it has a value';
	$template['[xfnotgiven_X] and [/xfnotgiven_X]'] = 'Only shows if the XField with the name "X" is empty.';

return $template;
}

add_filter('news-show-generic', 'call_xfields');

function call_xfields(){
global $row, $output;

    $xfieldsaction = 'templatereplace';
    $xfieldsinput  = $output;
    $xfieldsid     = $row['id'];
    include plugins_directory.'/xfields/core.php';
    $output        = $xfieldsoutput;

return $output;
}

add_filter('cutenews-options', 'xfields_AddToOptions');
add_action('plugin-options','xfields_CheckAdminOptions');

function xfields_AddToOptions($options) {
global $PHP_SELF;

	$options[] = array(
		'name'	 => 'XFields',
		'url'	 => $PHP_SELF.'?mod=options&amp;action=xfields&amp;xfieldsaction=configure',
		'access' => 1
	);

return $options;
}

function xfields_CheckAdminOptions(){

	if ($_GET['action'] == 'xfields'){
		xfields_AdminOptions();
	}
}

function xfields_AdminOptions(){
global $PHP_SELF, $cutepath, $_GET, $xfieldsadd, $xfieldsaction, $xfield;

	foreach ($_GET as $k => $v){
		$$k = $v;
	}

	include plugins_directory.'/xfields/core.php';
}

add_action('new-advanced-options', 'admins_xfields', 2);
add_action('edit-advanced-options', 'admins_xfields', 2);

function admins_xfields(){
global $xfield, $id, $mod;

    ob_start();
    $xfieldsaction = 'list';
    $xfieldsadd = ($mod == 'addnews' ? true : false);
    $xfieldsid = ($mod == 'addnews' ? '' : $id);
    include plugins_directory.'/xfields/core.php';
    $xfields = ob_get_contents();
    ob_get_clean();

return $xfields;
}

add_action('mass-deleted', 'xfields_delete', 2);

function xfields_delete(){
global $row;

    $xfieldsaction = 'delete';
    $xfieldsid = $row['id'];
    include plugins_directory.'/xfields/core.php';
}

add_action('new-save-entry', 'call_xfields_Save');
add_action('edit-save-entry', 'call_xfields_Save');

function call_xfields_Save(){
global $id, $xfield;

	$xfieldsid = $id;
	$xfieldsaction = 'init';
    include plugins_directory.'/xfields/core.php';
	$xfieldsaction = 'save';
	include plugins_directory.'/xfields/core.php';
}

add_filter('help-sections', 'xfields_help');

function xfields_help($help_sections){
$help_sections['xfields'] = <<<HTML
<h1>Understanding XFields</h1>
<p>
  With XFields, you can add custom fields to your CuteNews installation. These
  fields are like &quot;Title&quot;, &quot;Avatar URL&quot;,
  &quot;Short Story&quot; and &quot;Full Story&quot;. You might
  say: &quot;So, why is this addon so powerfull?&quot;. Well with this addon,
  CuteNews will be even more flexible.
</p>
<p>
  For example: For a clan site, you need an page where all
  matches are displayed on. You might want to do this with CuteNews. Your table
  always need the same layout, so make XFields and use them in your templates.
  Now you can just make fields for &quot;Enemy&quot;, &quot;Server IP&quot;,
  etc.
</p>
<p>
  Another example: If you're writing news posts about new games released, you
  could have a default place for screenshots, but don't want to put the HTML
  code in your news post everytime, you could use XFields. You might just put
  the screenshot code in your templates, and in your following news posts, just
  fill in the XField for the screenshot with the filename and it will show up as
  you did before, but a lot easier.
</p>
<p>
  Final example: If you have a site, made in two languages and you just want to
  use one CuteNews news post in two languages, you could use XFields. Just make
  one or two multiline XFields and an title XField. Now use them in diffrent
  templates. In your site just use the diffrent templates, and you will get the
  posts in the proper language.
</p>
<p>
  This addon was made by <a href="mailto:smk2@xs4all.nl">SMKiller2</a>.
</p>
<h1>Creating and editing XFields</h1>
<p>
  These are the fields that should be filled in when you edit or add a XField:
</p>
<p>
  <b>Name:</b> The internal name the XField should use. This should be unique
  for each XField and is not displayed anywhere.
</p>
<p>
  <b>Description:</b> The description showed when adding or editing news.
</p>
<p>
  <b>Category:</b> Select the category of the news item where the XField should
  appear. If you select custom, another field appears where you can select
  multiple categories.
</p>
<p>
  <b>Type:</b> Select the behavoir of the XField. You can shoose between three
  diffrent types.
</p>
<p>
  <b>Default:</b> You will only get this field when you have selected either
  &quot;Multi Line&quot; or &quot;Single Line&quot; as type. You may enter a
  default value for the XField here.
</p>
<p>
  <b>Options:</b> You will only get this field when you have selected
  &quot;Dropdown Listbox&quot; as type. You may enter all options that should
  appear in this field here. You cannot use two exacly the same options.
</p>
<p>
  <b>Optional:</b> You will only get this checkbox when you have selected either
  &quot;Multi Line&quot; or &quot;Single Line&quot; as type. You can select
  whether the field is optional or not here.
</p>
<p>
  &nbsp;
</p>
<h1>Configuring Templates</h1>
<p>
  You can use the values entered in the XFields in your templates, just use the
  following tags:
</p>
<p>
  <b>[xfvalue_NAME]:</b> This will output the XField with the same name as
  provided in the tag as NAME.
</p>
<p>
  eg. My current mood is: &amp;quot;[xfvalue_mood]&amp;quot;.<br />
  Will display: My current mood is: &quot;happy&quot;. When you entered it in
  the XField. But note that if the XField is optional and you haven't entered a
  value, it will show: My current mood is: &quot;&quot;.
</p>
<p>
  <b>[xfgiven_NAME]...[/xfgiven_NAME]:</b> This is only supported for optional
  XFields. It will only show the code between the tags if the XField is filled
  with a value. Otherwise it won't show anything.
</p>
<p>
  <b>[xfnotgiven_NAME]...[/xfnotgiven_NAME]:</b> This is only supported for optional
  XFields. It will only show the code between the tags if the XField is empty. Otherwise it won't show anything.
</p>
<p>
  eg. [xfgiven_mood] My current mood is:
  &amp;quot;[xfvalue_mood]&amp;quot;. [/xfgiven_mood][xfnotgiven_mood]No mood today.[/xfnotgiven_mood]<br />
  Will display: My current mood is: &quot;happy&quot;. When you entered
  &quot;happy&quot; in the XField. If you haven't entered anything in the
  XField, it will show &quot;No mood today.&quot;.
</p>
<p>
  This addon was made by <a href="mailto:smk2@xs4all.nl">SMKiller2</a>.
</p>
HTML;

return $help_sections;
}
?>