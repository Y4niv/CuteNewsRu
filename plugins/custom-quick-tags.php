<?php
/*
Plugin Name:	Custom Quick Tags
Plugin URI:		http://cutenews.ru/cat/plugins/
Description:	Create custom quicktags. You can use <code>{bbcodes}</code> in the templates to show all the quick tags. Or use <code>{bbcodes:N}</code>, where <b>N</b> is the number of quick tags per line.
Version:		1.0
Author:			David Carrington
Author URI:		http://www.brandedthoughts.co.uk
Required Framework: 1.1.2
Application:	CuteNews
*/



add_filter('cutenews-options', 'cqt_AddToOptions');
add_action('plugin-options','cqt_CheckAdminOptions');

add_filter('news-entry-content','apply_cqt');
add_filter('news-comment-content','apply_cqt');

add_filter('add-comment-box', 'cqt_insertBBCodes');

add_filter('template-variables-commentform','cqt_macros_variables');

function cqt_macros_variables($output){

	$output['{bbcodes} or {bbcode:N}'] = 'Show the bbcodes(N = bbcodes per line)';

return $output;
}

function cqt_insertBBCodes($output){

	$cqt = new PluginSettings('Custom_Quick_Tags');

	preg_match('[{bbcodes:([0-9]+)}]i', $output, $br);

	if ($cqt->settings['tags']){
		$i = 0;

		foreach($cqt->settings['tags'] as $cqt){
			$i++;

			$echo['cqt'] .= ' <nobr><a href="javascript:insertext(\'['.$cqt['tag'].']\', \'[/'.$cqt['tag'].']\', \'short\')">['.$cqt['tag'].']<'.$cqt['tag'].'>'.$cqt['name'].'</'.$cqt['tag'].'>[/'.$cqt['tag'].']</a></nobr> '.(($br[1] and ($i%$br[1] == 0)) ? '<br>' : '');
		}
	}

	$output = preg_replace('[{bbcodes:([0-9]+)}]i', $echo['cqt'], $output);
	$output = str_replace('{bbcodes}', $echo['cqt'], $output);

return $output;
}

function cqt_AddToOptions($options, $hook) {
global $PHP_SELF;

	// Add a custom screen to the "options" screen
	$options[] = array(
		'name'		=> 'Quick Tags',
		'url'		=> $PHP_SELF.'?mod=options&amp;action=cqt',
		'access'	=> '1',
	);

	// return the customized options
	return $options;
}

//
function cqt_CheckAdminOptions($hook) {
	// chek if the user is requesting the CQT options
	if ($_GET['action'] == 'cqt')
		// show the CQT admin screen
		cqt_AdminOptions();
}

function cqt_AdminOptions() {
	echoheader('user','Custom Quick Tags');

	$cqt = new PluginSettings('Custom_Quick_Tags');

	switch ($_GET['subaction']) {
		case 'edit':
			$tag = $cqt->settings['tags'][$_GET['id']];
		case 'add':
			$id = $tag ? '&amp;id='.$_GET['id'] : '';
			$buffer = '
<table cellspacing="0" cellpadding="0">
	
	<tr><td>&nbsp;</tr>
   </table>
	<form method="post" action="?mod=options&amp;action=cqt&amp;subaction=doadd'.$id.'" class="easyform">
		<div>
			<label for="txtName"><b>Name</b></label><br />
			<input id="txtName" name="cqt[name]" value="'.$tag[name].'" style="width: 400px;" />
		</div><br />
		<div>
			<label for="txtTag"><b>Tag</b></label><br />
			<input id="txtTag" name="cqt[tag]" value="'.$tag[tag].'" style="width: 400px;" />
		</div><br />
		<div>
			<label for="txtReplace"><b>Replace with...</b></label><br />
			<textarea id="txtReplace" name="cqt[replace]" style="width: 400px; height: 150px;">'.$tag[replace].'</textarea><br />
		</div>
		<div>
			<input type="checkbox" id="txtComplex" name="cqt[complex]"'.($tag[complex] ? ' checked="checked"' : '').' value="true" style="border: 0px;" />
			<label for="txtComplex">Complex</label>
		<input type="checkbox" id="txtShort" name="cqt[short]"'.($tag[short] ? ' checked="checked"' : '').' value="true" style="border: 0px;" /><label for="txtShort">Short</label></div><br />
		<input type="submit" value="Edit/Add" />
	</form>';
			break;


		case 'delete':
			$tag = $cqt->settings['tags'][$_GET['id']];
			if ($tag[name])
				$buffer = '<p>Deleted: <strong>'.$tag[name].'</strong></p>';
			unset($cqt->settings['tags'][$_GET['id']]);
			$cqt->save();
			break;


		case 'doadd':
			$tag = array(
				name	=> stripslashes($_POST[cqt][name]),
				tag		=> stripslashes($_POST[cqt][tag]),
				complex	=> stripslashes($_POST[cqt][complex]),
								short => stripslashes($_POST[cqt][short]),
				replace	=> stripslashes($_POST[cqt][replace]),
			);

			if ($_GET['id'])
				$cqt->settings['tags'][$_GET['id']] = $tag;
			else
				$cqt->settings['tags'][] = $tag;

			$buffer = '<p>Added: <strong>'.$_POST[cqt][name].'</strong></p>';
			$cqt->save();


		default:
			$buffer .= '
		<table border=0 cellpadding=2 cellspacing=2 width=100%>
			<tr>
				<td bgcolor=#F7F6F4>&nbsp;<b>Name</b></td>
				<td bgcolor=#F7F6F4><b>Tag</b></td>
				<td bgcolor=#F7F6F4><b>Replacement</b></td>
				<td bgcolor=#F7F6F4><b>Actions</b></td>
			</tr>';

			$tags = $cqt->settings['tags'];

			if (empty($tags)) {
				$buffer .= '<td colspan="5">No tags available</td>';
			} else
				foreach ($cqt->settings['tags'] as $id => $tag) {
					$buffer .= '
			<tr>
				<td>&nbsp;'.$tag[name].'</td>
				<td>['.$tag[tag].']</td>
				<td>'.htmlspecialchars($tag[replace]).'</td>
				<td><a href="?mod=options&amp;action=cqt&amp;subaction=edit&amp;id='.$id.'" title="Edit tag '.$tag[tag].'">Edit</a> <a href="?mod=options&amp;action=cqt&amp;subaction=delete&amp;id='.$id.'" title="Delete tag '.$tag[tag].'">Delete</a></td>
				</tr>';
				}

			$buffer .= '
		</table>
		<p><a href="?mod=options&amp;action=cqt&amp;subaction=add">Add new tag</a></p>';
	}

	echo $buffer;

	echofooter();
}

function apply_cqt($content, $hook) {
		$cqt = new PluginSettings('Custom_Quick_Tags');
		$tags = $cqt->settings['tags'];
		if (!empty($tags))
				foreach ($tags as $tag){
			if ($tag[complex] == 'true'){
				$content = preg_replace('{\['.$tag['tag'].'=([^[]*)\](.*)\[\/'.$tag['tag'].'\]}i',
$tag[replace], $content);
			}
			elseif ($tag[short] == 'true') {
				$content = preg_replace('{\['.$tag['tag'].'=([^[]*)\]}i', $tag[replace], $content);
			}

						else {
								$content = preg_replace('{\['.$tag['tag'].'\](.*)\[\/'.$tag['tag'].'\]}iU',
$tag['replace'], $content);
						}
				}

		return $content;
}

?>