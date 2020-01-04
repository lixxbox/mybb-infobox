<?php
/*
"Infobox" for MyBB
Add MyCodes for infoboxes and extdable infoboxes.
lixxbox@3DDC
04.01.2020
*/

// Make sure we can't access this file directly from the browser.
if(!defined("IN_MYBB")){
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

// hooks
$plugins->add_hook("parse_message", "infobox_run");

function infobox_info(){
global $mybb;
	return array(
		"name"			=> "Infobox",
		"description"	=> "Add MyCodes for infoboxes and expandable infoboxes. [info] & [infoext]",
		"website"		=> "https://github.com/lixxbox/mybb-infobox",
		"author"		=> "lixxbox",
		"authorsite"	=> "https://github.com/lixxbox",
		"version"		=> "1.0",
		"codename"		=> "infobox",
		"compatibility"	=> "18*",
	);
}

// plugin is activated
function infobox_activate(){
	// create stylesheets
	global $db, $mybb;
	$query_tid = $db->write_query("SELECT tid FROM ".TABLE_PREFIX."themes WHERE def='1'");
	$themetid = $db->fetch_array($query_tid);
	$styles = array(
		'name'         => 'infobox.css',
		'tid'          => $themetid['tid'],
		'attachedto'   => 'showthread.php|newthread.php|newreply.php|editpost.php|private.php|announcements.php|portal.php',
		'stylesheet'   => '
.InfoExt {
border-radius: 5px;
border: 1px solid #CCC;
padding: 10px;
background-color: #fff;
padding-bottom: 0px;
margin-top: 5px;
}

.Info {
border-radius: 5px;
border: 1px solid #CCC;
padding: 10px;
background-color: #fff;
padding-bottom: 0px;
margin-top: 5px;
}

.InfoTitle {
font-weight: bold;
font-size: 1.2em;
padding-bottom: 10px;
}

.InfoTitle .fa {
}

.arrow {
-moz-transition: all 0.4s linear;
-webkit-transition: all 0.4s linear;
transition: all 0.4s linear;
}

.arrow.down{
-ms-transform: rotate(90deg);
-moz-transform: rotate(90deg);
-webkit-transform: rotate(90deg);
transform: rotate(90deg);
}

.InfoText {
	padding: 10px 5px 10px 5px;
	border-top: 1px solid #CCC;
}

.InfoText .close {
	float: right;
	display: none; /* disabled */
}',
'lastmodified' => TIME_NOW
);
					
	$sid = $db->insert_query('themestylesheets', $styles);
	$db->update_query('themestylesheets', array('cachefile' => "css.php?stylesheet={$sid}"), "sid='{$sid}'", 1);
	$query = $db->simple_select('themes', 'tid');
	while($theme = $db->fetch_array($query))
	{
		require_once MYBB_ADMIN_DIR.'inc/functions_themes.php';
		update_theme_stylesheet_list($theme['tid']);
	}
}

// plugin is deactivated
function infobox_deactivate(){
	global $db;
	$db->delete_query('themestylesheets', "name='infobox.css'");
	$query = $db->simple_select('themes', 'tid');
	while($theme = $db->fetch_array($query))
	{
		require_once MYBB_ADMIN_DIR.'inc/functions_themes.php';
		update_theme_stylesheet_list($theme['tid']);
	}
}

// called by hooks
function infobox_run(&$message){
	global $db, $mybb;
    //$lang->load("infobox", false, true);
	
	// INFOBOX
	// default - icon: fa-info title: Hinweis
	$regex = '#\[info\](.*?)\[\/info\]#si';
	while(preg_match($regex,$message))
	{
		$message = preg_replace($regex,
		'<div class="Info"><div class="InfoTitle"><i class="fa fa-fw fa-info"></i>&nbsp;Hinweis</div><div class="InfoText">$1</div></div>',$message);
	}
		
	// custom icon - custom title
	$regex = '#\[info=(.*?)( icon=(.*?))?\](.*?)\[\/info\]#si';
	while(preg_match($regex,$message))
	{
		$message = preg_replace($regex,
		'<div class="Info"><div class="InfoTitle"><i class="fa fa-fw $3"></i>&nbsp;$1</div><div class="InfoText">$4</div></div>',$message);
	}
		
	// INFOBOX EXTENDABLE
	// default - icon: fa-info title: Hinweis
	$regex = '#\[infoext\](.*?)\[\/infoext\]#si';
	while(preg_match($regex,$message))
	{
		$message = preg_replace($regex,
		'<div class="InfoExt"><div style="cursor:pointer;" class="InfoTitle" onclick="jQuery(this).next().slideToggle(); jQuery(this).children(\'.arrow\').toggleClass(\'down\');"><i class="fa fa-fw fa-info"></i><i class="arrow fa fa-fw fa-caret-right"></i>&nbsp;Hinweis</div><div class="InfoText" style="display: none;">$1<i style="cursor:pointer;" class="close fa fa-fw fa-times" onclick="jQuery(this).parent().slideToggle(); jQuery(this).parent().prev().children(\'.arrow\').toggleClass(\'down\');" title="Schließen"></i></div></div>',$message);
	}
	
	// custom icon - custom title
	$regex = '#\[infoext=(.*?)( icon=(.*?))?\](.*?)\[\/infoext\]#si';
	while(preg_match($regex,$message))
	{
		$message = preg_replace($regex,
		'<div class="InfoExt"><div style="cursor:pointer;" class="InfoTitle" onclick="jQuery(this).next().slideToggle(); jQuery(this).children(\'.arrow\').toggleClass(\'down\');"><i class="fa fa-fw $3"></i><i class="arrow fa fa-fw fa-caret-right"></i>&nbsp;$1</div><div class="InfoText" style="display: none;">$4<i style="cursor:pointer;" class="close fa fa-fw fa-times" onclick="jQuery(this).parent().slideToggle(); jQuery(this).parent().prev().children(\'.arrow\').toggleClass(\'down\');" title="Schließen"></i></div></div>',$message);
	}

	return $message;
}