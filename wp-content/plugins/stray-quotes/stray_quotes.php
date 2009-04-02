<?php
/*
Plugin Name: Stray Random Quotes
Plugin URI: http://www.italyisfalling.com/stray-random-quotes/
Description: Display and rotate random quotes and random words everywhere on your blog. Easy to custom and manage. Ajax enabled.
Author: ico@italyisfalling.com
Author URI:http://www.italyisfalling.com/lines-of-code/
Version: 1.9.0
License: GPL compatible
*/

global $wpdb, $wp_version;

//few definitions
define("WP_STRAY_QUOTES_TABLE", $wpdb->prefix . "stray_quotes");
define ("DIR",basename(dirname(__FILE__)));
if (DIR == 'plugins') $dir = '';
define("WP_STRAY_QUOTES_PATH", get_option("siteurl") . "/wp-content/plugins/" . DIR);

// !!! remember to change this with every new version !!!
define("WP_STRAY_VERSION", 190);

//get ready for local
$currentLocale = get_locale();
if(!empty($currentLocale)) {
	$moFile = ABSPATH . 'wp-content/plugins/' . $dir . 'lang/stray-quotes-' . $currentLocale . ".mo";
	//check if it is a window server and change path accordingly
	/*if ( strpos($moFile, '\\')) $moFile = str_replace('/','\\',$moFile);*/ 
	if(@file_exists($moFile) && is_readable($moFile)) load_textdomain('stray-quotes', $moFile);
}

// fix REQUEST_URI for ISS
if ( !isset($_SERVER['REQUEST_URI']) || ($_SERVER['REQUEST_URI']=='') ) {

	$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'],1);

	if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != '') {
		$_SERVER['REQUEST_URI'] .= '?'.$_SERVER['QUERY_STRING'];
	}
}

//add ajax script
function stray_quotes_add_js() {
	$quotesoptions = get_option('stray_quotes_options');
	if ($quotesoptions['stray_ajax'] !='Y') wp_enqueue_script('stray_quotes_ajax_script', WP_STRAY_QUOTES_PATH.'/inc/js_ajax.js', array('jquery'));
}

//add header
function stray_quotes_header(){
	
	//header for the manage page
	if(strpos($_SERVER['REQUEST_URI'],'stray_manage')) {
	
		?><script  type='text/javascript'><!-- 
		function switchpage(select) {var index;for(index=0; index<select.options.length; index++) {if(select.options[index].selected){if(select.options[index].value!="")window.location.href=select.options[index].value;break;}}} 

		jQuery(document).ready(function($) {
			$("#straymanage thead tr th:first input:checkbox").click(function() {
				var checkedStatus = this.checked;
				$("#straymanage tbody tr td:first-child input:checkbox").each(function() {
					this.checked = checkedStatus;
				});
			});
	
		});
		
		
		function disable_enable(){
			if (document.getElementById('bulkselect').value == 'changecategory')document.getElementById('catselect').disabled=false;
			else document.getElementById('catselect').disabled=true;
		}
		// Multiple onload function created by: Simon Willison
		// http://simonwillison.net/2004/May/26/addLoadEvent/
		function addLoadEvent(func) {
		  var oldonload = window.onload;
		  if (typeof window.onload != 'function') {
			window.onload = func;
		  } else {
			window.onload = function() {
			  if (oldonload) {
				oldonload();
			  }
			  func();
			}
		  }
		}
		addLoadEvent(function() {
			document.getElementById('catselect').disabled = true;
		});

        --></script><?php	
	}

	//header for the settings page
	if(strpos($_SERVER['REQUEST_URI'],'stray_quotes_options')) {
	
		?><script type="text/javascript">
		function disable_enable(){
			var a = document.getElementById('ajaxinput1');
			var b = document.getElementById('ajaxinput2');
			var c = document.getElementById('ajaxinput3');
			var d = document.getElementById('ajaxinput4');
			if (a.disabled==true)a.disabled=false;
			else a.disabled=true;
			if (b.disabled==true)b.disabled=false;
			else b.disabled=true;
			if (c.disabled==true)c.disabled=false;
			else c.disabled=true;
			if (d.disabled==true)d.disabled=false;
			else d.disabled=true;
		}
		// Multiple onload function created by: Simon Willison
		// http://simonwillison.net/2004/May/26/addLoadEvent/
		function addLoadEvent(func) {
		  var oldonload = window.onload;
		  if (typeof window.onload != 'function') {
			window.onload = func;
		  } else {
			window.onload = function() {
			  if (oldonload) {
				oldonload();
			  }
			  func();
			}
		  }
		}
		addLoadEvent(function() {
			disablefields();
		});
		</script><?php
	
	}
}

//upon activation
function quotes_activation() {

	global $wpdb;
	
	//set the messages
	$straymessage = "";
	$newmessage = str_replace("%1","http://www.italyisfalling.com/stray-random-quotes/#changelog",__('<p>Hey. Welcome to a new version of <strong>Stray Random Quotes</strong>. All changes are addressed in the <a href="%1">changelog</a>, but you should know that: </p>','stray-quotes'));
	
	//in case we have to point to other pages in the messages
	$widgetpage = get_option('siteurl')."/wp-admin/widgets.php";
	$management = get_option('siteurl')."/wp-admin/admin.php?page=stray_manage";
	$options =  get_option('siteurl')."/wp-admin/admin.php?page=stray_quotes_options";
	$new = get_option('siteurl')."/wp-admin/admin.php?page=stray_new";
	$help =  get_option('siteurl')."/wp-admin/admin.php?page=stray_help";

	//check if table exists and alter it if necessary	
	$straytableExists = false;
	$straytables = $wpdb->get_results("SHOW TABLES");
	$wp_quotes = $wpdb->prefix . "quotes";
	foreach ( $straytables as $straytable ){	
		foreach ( $straytable as $value ){
			
			//takes care of the old wp_quotes table (probably useless)
			if ( $value == $wpdb->prefix . "quotes" ){
					
				$straytableExists = true;	
				//if table exists it must be old -- must update and rename.
				$wpdb->query('ALTER TABLE ' . $wp_quotes . ' ADD COLUMN `source` VARCHAR( 255 ) NOT NULL AFTER `author`');
				$wpdb->query('ALTER TABLE ' . $wp_quotes . ' ADD COLUMN `category` VARCHAR( 255 ) NOT NULL  DEFAULT "default" AFTER `source`');			
				
				//and fill in default values
				$wpdb->query('UPDATE '. $wp_quotes . ' SET `category`="default"');				
				$wpdb->query('RENAME TABLE ' . $wp_quotes . ' TO ' . WP_STRAY_QUOTES_TABLE);
				
				//message
				$search = array("%s1", "%s2");
				$replace = array($wp_quotes, WP_STRAY_QUOTES_TABLE);
				if (!$straymessage) $straymessage = $newmessage;
				$straymessage .= str_replace($search,$replace,__('<li>* I changed the old table "%s1" into a new one called "%s2" but don\'t worry, all your quotes are still there.</li>','stray-quotes')); 
				
				break;
			}
			
			//takes care of the new table
			if ( $value == WP_STRAY_QUOTES_TABLE ){			
				
				$categoryCol = $wpdb->get_col('SELECT `category` FROM '.WP_STRAY_QUOTES_TABLE);
				$groupCol = $wpdb->get_col('SELECT `group` FROM '.WP_STRAY_QUOTES_TABLE);
				if (!$categoryCol && !$groupCol) {
				
					//add new field
					$wpdb->query('ALTER TABLE ' . WP_STRAY_QUOTES_TABLE . ' ADD COLUMN `category` VARCHAR( 255 ) NOT NULL DEFAULT "default" AFTER `source`');
					
					//and fill in default category values
					$wpdb->query('UPDATE '. WP_STRAY_QUOTES_TABLE . ' SET `category`="default"');
					
					//message
					$search = array("%s1", "%s2");
					$replace = array(WP_STRAY_QUOTES_TABLE,  get_option('siteurl')."/wp-admin/admin.php?page=stray_manage");
					if (!$straymessage) $straymessage = $newmessage;
					$straymessage .= str_replace($search,$replace,__('<li>* This plugin now comes with "categories", which should make for a more intelligent way to organize, maintain and display quotes on your blog. I updated the table "%s1" but all your quotes <a href="%s2">are still there</a>.</li>','stray-quotes')); 
	
				}
						
				$straytableExists=true;			
				break;	
			}		
		}
	}
	
	//table does not exist, create one
	if ( !$straytableExists ) {
		
		$wpdb->query("
		CREATE TABLE IF NOT EXISTS `". WP_STRAY_QUOTES_TABLE . "` (
		`quoteID` INT NOT NULL AUTO_INCREMENT ,
		`quote` TEXT NOT NULL ,
		`author` varchar( 255 ) NOT NULL ,
		`source` varchar( 255 ) NOT NULL ,
		`category` varchar( 255 ) NOT NULL  DEFAULT 'default',
		`visible` ENUM( 'yes', 'no' ) NOT NULL DEFAULT 'yes',
		PRIMARY KEY ( `quoteID` ) )
		");
		
		//insert sample quote
		$wpdb->query("INSERT INTO " . WP_STRAY_QUOTES_TABLE . " (
		`quote`, `author`, `source`) values ('Always tell the truth. Then you don\'t have to remember anything.', 'Mark Twain', 'Roughin\' it') ");
		
		//message
		$straymessage = __('Hey. This seems to be your first time with this plugin. I\'ve just created the database table "%s1" to store your quotes, and added one to start you off.','stray-quotes');
	}
	
	$quotesoptions = get_option('stray_quotes_options');
		
	//convert old options into (or insert the) new array options	
	if (false === $quotesoptions || !is_array($quotesoptions) || $quotesoptions=='' ) {
		
		$quotesoptions = array();
		
		//conversion of old pre-1.7 options AND/OR creating new options
		$var = 'stray_quotes_before_all';
		$temp = get_option($var);
		if (false === $temp) $quotesoptions[$var] =  '';
		else $quotesoptions[$var] = $temp;
		delete_option($var);
		unset($var);unset($temp);		
		$var = 'stray_quotes_before_quote';
		$temp = get_option($var);
		if (false === $temp) $quotesoptions[$var] =  '&#8220;';
		else $quotesoptions[$var] = $temp;
		delete_option($var);		
		unset($var);unset($temp);
		$var = 'stray_quotes_after_quote';
		$temp = get_option($var);
		if (false === $temp) $quotesoptions[$var] =  '&#8221;';
		else $quotesoptions[$var] = $temp;
		delete_option($var);		
		unset($var);unset($temp);
		$var = 'stray_quotes_before_author';
		$temp = get_option($var);
		if (false === $temp) $quotesoptions[$var] =  '<br/>by&nbsp;';
		else $quotesoptions[$var] = $temp;
		delete_option($var);		
		unset($var);unset($temp);
		$var = 'stray_quotes_after_author';
		$temp = get_option($var);
		if (false === $temp) $quotesoptions[$var] =  '';
		else $quotesoptions[$var] = $temp;
		delete_option($var);		
		unset($var);unset($temp);
		$var = 'stray_quotes_before_source';
		$temp = get_option($var);
		if (false === $temp) $quotesoptions[$var] =  '<em>&nbsp;';
		else $quotesoptions[$var] = $temp;
		delete_option($var);		
		unset($var);unset($temp);
		$var = 'stray_quotes_after_source';
		$temp = get_option($var);
		if (false === $temp) $quotesoptions[$var] =  '</em>';
		else $quotesoptions[$var] = $temp;
		delete_option($var);		
		unset($var);unset($temp);
		$var = 'stray_quotes_after_all';
		$temp = get_option($var);
		if (false === $temp) $quotesoptions[$var] =  '';
		else $quotesoptions[$var] = $temp;
		delete_option($var);		
		unset($var);unset($temp);
		$var = 'stray_quotes_put_quotes_first';
		$temp = get_option($var);
		if (false === $temp) $quotesoptions[$var] =  'Y';
		else $quotesoptions[$var] = $temp;
		delete_option($var);		
		unset($var);unset($temp);
		$var = 'stray_quotes_default_visible';
		$temp = get_option($var);
		if (false === $temp) $quotesoptions[$var] =  'Y';
		else $quotesoptions[$var] = $temp;
		delete_option($var);
		unset($var);unset($temp);
		
		//only remove
		$var = 'stray_quotes_widget_title';
		$temp = get_option($var);
		if ($temp)delete_option($var);		
		unset($var);unset($temp);
		$var = 'stray_quotes_regular_title';
		$temp = get_option($var);
		if ($temp)delete_option($var);		
		unset($var);unset($temp);
		
		//special trasformation for how link options work now
		$var = 'stray_quotes_use_google_links';
		$temp = get_option($var);
		$varb = 'stray_quotes_wiki_lan';		
		$tempb = get_option($varb);
		if ($temp == 'Y') {
			$quotesoptions['stray_quotes_linkto'] = '<a href="http://www.google.com/search?q=&quot;%AUTHOR%&quot;">';
			$quotesoptions['stray_quotes_sourcelinkto'] = '<a href="http://www.google.com/search?q=&quot;%SOURCE%&quot;">';
			$quotesoptions['stray_quotes_sourcespaces'] = ' ';	
			$quotesoptions['stray_quotes_authorspaces'] = ' ';		
		} 
		
		else if ($temp == 'W') {
			$quotesoptions['stray_quotes_linkto'] = '<a href="http://'.$tempb.'.wikipedia.org/wiki/%AUTHOR%';
			$quotesoptions['stray_quotes_linkto'] = '<a href="http://'.$tempb.'.wikipedia.org/wiki/%SOURCE%';
			$quotesoptions['stray_quotes_sourcespaces'] = '_';	
			$quotesoptions['stray_quotes_authorspaces'] = '_';		
		}
		
		else {
			$quotesoptions['stray_quotes_linkto'] =  '';
			$quotesoptions['stray_quotes_sourcelinkto'] =  '';
			$quotesoptions['stray_quotes_sourcespaces'] = '-';	
			$quotesoptions['stray_quotes_authorspaces'] = '-';		
		}
		delete_option($var);
		delete_option($varb);
		
		//more new entries
		$quotesoptions['stray_if_no_author'] =  '<br/>source:&nbsp;';	
		$quotesoptions['stray_quotes_uninstall'] = '';
		$quotesoptions['stray_clear_form'] =  'Y';	
		$quotesoptions['stray_quotes_order'] = 'quoteID';
		$quotesoptions['stray_quotes_rows'] = 10; 
		$quotesoptions['stray_quotes_categories'] = 'all';
		$quotesoptions['stray_quotes_sort'] = 'DESC';
		$quotesoptions['stray_default_category'] =  'default';	
		$quotesoptions['stray_quotes_version'] = WP_STRAY_VERSION; 
		$quotesoptions['stray_before_loader'] = '<p align="left">';
		$quotesoptions['stray_loader'] = '';
		$quotesoptions['stray_after_loader'] = '</p>';
		$quotesoptions['stray_ajax'] =  '';
		$quotesoptions['comment_scode'] =  '';
		$quotesoptions['title_scode'] =  '';
		$quotesoptions['excerpt_scode'] =  '';
		$quotesoptions['widget_scode'] =  '';
		$quotesoptions['categories_scode'] =  '';
		$quotesoptions['tags_scode'] =  '';
		$quotesoptions['bloginfo_scode'] =  '';		
		$quotesoptions['bookmarlet_source'] =  '';
		$quotesoptions['bookmarklet_cat'] =  '';
		$quotesoptions['stray_loading'] =  'loading...';
				
		//the message
		delete_option('stray_quotes_first_time');		
		if (!$straymessage) $straymessage = $newmessage;
		$straymessage .=__('<li>* I converted your old settings into new ones that will take less room in your database and be faster to load.</li>','stray-quotes');
		
	}
				
	// <= 1.7.3
	if( $quotesoptions['stray_quotes_version'] <= 172 )$quotesoptions['stray_default_category'] =  'default';
	
	// <= 1.7.6
	if( $quotesoptions['stray_quotes_version'] <= 175 ){
		//add a new fields
		$quotesoptions['stray_if_no_author'] =  '';
		$quotesoptions['stray_clear_form'] =  'Y';	
	}
		
	// <= 1.7.9
	if ( $quotesoptions['stray_quotes_version'] <= 178 ){ 
	
		//because of the mess caused by 1.7.8, there's a chance that the user has two category columns now! (ugh!)		
		$categorycol = $wpdb->get_col('SELECT `category` FROM '.WP_STRAY_QUOTES_TABLE);
		$groupcol = $wpdb->get_col('SELECT `group` FROM '.WP_STRAY_QUOTES_TABLE);
		
		//if there are two columns
		if ($categorycol && $groupcol) {
		
			//make sure the two columns are identical (this will loose changes made to this field after 178, but it is the less painful way.)
			$wpdb->query('UPDATE ' . WP_STRAY_QUOTES_TABLE . ' SET `category` = `group`');
			
			//drop the old one
			$wpdb->query("ALTER TABLE `".WP_STRAY_QUOTES_TABLE."` DROP COLUMN `group`");
			
			//message
			if (!$straymessage)$straymessage = $newmessage;
			$needed = true;
			
		}
		
		//if there is only "group"
		else if ($groupcol && !$categorycol) {
			
			//add new field
			$wpdb->query('ALTER TABLE ' . WP_STRAY_QUOTES_TABLE . ' ADD COLUMN `category` VARCHAR( 255 ) NOT NULL DEFAULT "default" AFTER `group`');
			
			// copy values
			$wpdb->query('UPDATE ' . WP_STRAY_QUOTES_TABLE . ' SET `category` = `group`');
			
			//drop the old one
			$wpdb->query("ALTER TABLE `".WP_STRAY_QUOTES_TABLE."` DROP COLUMN `group`");
			
			//message
			if (!$straymessage)$straymessage = $newmessage;
			$needed = true;
		}
		
		//feedback the above
		if ($needed == true)$straymessage .=__('<li>* hopefully the mess caused by version 1.7.8 has now been corrected... If you changed the categories assigned to quotes using the 1.7.8 version, you might find now that the changes made are lost. All the rest will stay the same, including that "groups" are now called "categories".</li>','stray-quotes');
	
		//if there are spaces in category (corrected in 1.7.6, might have slipped because of the mess of 1.7.8!)
		$removal = $wpdb->query("UPDATE `".WP_STRAY_QUOTES_TABLE."` SET `category`= REPLACE(`category`, ' ', '-') WHERE `category` LIKE '% %'");
		if ($removal)$straymessage .=__('<li>* spaces are not allowed within categories names, because they created all sorts of problems. I replaced them with dashes. I hope it\'s okay.</li>','stray-quotes');
	
		//options that have changed their names (operation missing in 1.7.8)
		if ($quotesoptions['stray_quotes_groups'] !== false) {
			$quotesoptions['stray_quotes_categories'] = $quotesoptions['stray_quotes_groups'];
		}else $quotesoptions['stray_quotes_categories'] = "all";	
		unset($quotesoptions['stray_quotes_groups']);
		
		if ($quotesoptions['stray_default_group'] !== false) {
			$quotesoptions['stray_default_category'] = $quotesoptions['stray_default_group'];
		} else $quotesoptions['stray_default_category'] = "default";
		unset($quotesoptions['stray_default_group']);
			
		//also make sure this value is not "group"
		if ($quotesoptions['stray_quotes_order'] == 'group')$quotesoptions['stray_quotes_order'] = 'category';
		
	}

	// <= 1.8.0
	if ( $quotesoptions['stray_quotes_version'] <= 179 ){
	
		//add a new fields
		$quotesoptions['stray_before_loader'] = '<p>';
		$quotesoptions['stray_loader'] = '';
		$quotesoptions['stray_after_loader'] = '</p>';

	}
	
	// <= 1.8.2
	if( $quotesoptions['stray_quotes_version'] <= 181 ){
		//add a new fields
		$quotesoptions['stray_ajax'] =  '';
	}
	
	// <= 1.8.5
	if(  $quotesoptions['stray_quotes_version'] <= 184 ){
		//add a new fields
		$quotesoptions['comment_scode'] =  '';
		$quotesoptions['title_scode'] =  '';
		$quotesoptions['excerpt_scode'] =  '';
		$quotesoptions['widget_scode'] =  '';
		$quotesoptions['categories_scode'] =  '';
		$quotesoptions['tags_scode'] =  '';
		$quotesoptions['bloginfo_scode'] =  '';		
		$quotesoptions['bookmarlet_source'] =  '';
		$quotesoptions['bookmarklet_cat'] =  '';
	}
	
	// <= 1.8.6
	if(  $quotesoptions['stray_quotes_version'] <= 186 ){
		
		//add a new fields
		$quotesoptions['stray_loading'] =  'loading...';
		
		//remove obsolete fields
		unset($quotesoptions['stray_quotes_widget_title']);
		unset($quotesoptions['stray_quotes_regular_title']);
	}

	//take care of version number
	$quotesoptions['stray_quotes_version'] = WP_STRAY_VERSION; 
	
	//reset the removal option for everyone
	$quotesoptions['stray_quotes_uninstall'] = "";
	
	//insert the feedback message
	$quotesoptions['stray_quotes_first_time'] = $straymessage;

	//and finally we actually put the option thing in the database
	update_option('stray_quotes_options', $quotesoptions);		
	
}

//upon deactivation
function quotes_deactivation() {

	global $wpdb;

	$quotesoptions = get_option('stray_quotes_options');
	$sql = "DROP TABLE IF EXISTS ".WP_STRAY_QUOTES_TABLE;

	//delete the options
	if($quotesoptions['stray_quotes_uninstall'] == 'options') {
		delete_option('stray_quotes_options');
		delete_option('widget_stray_quotes');
	}
	else if ($quotesoptions['stray_quotes_uninstall'] == 'table')$wpdb->query($sql);
	else if ($quotesoptions['stray_quotes_uninstall'] == 'both'){
		 delete_option('stray_quotes_options');
		 delete_option('widget_stray_quotes');
		$wpdb->query($sql);
	}

}
	
//for compatibility
if ($wp_version <= 2.3 ) add_filter('the_content', 'wp_quotes_page', 10);

//includes
include('inc/stray_functions.php');
include('inc/stray_overview.php');
include('inc/stray_settings.php');
include('inc/stray_manage.php');
include('inc/stray_new.php');
include('inc/stray_widgets.php');
include('inc/stray_tools.php');
include('inc/stray_help.php');
include('inc/stray_remove.php');

//build submenu entries
function stray_quotes_add_pages() {

	add_menu_page('Stray Random Quotes', __('Quotes','stray-quotes'), 8, __FILE__, 'stray_intro', WP_STRAY_QUOTES_PATH.'/img/lightbulb.png');
	add_submenu_page(__FILE__, __('Overview for the Quotes','stray-quotes'), __('Overview','stray-quotes'), 8, __FILE__, 'stray_intro');
	add_submenu_page(__FILE__, __('Manage Quotes','stray-quotes'), __('Manage','stray-quotes'), 8, 'stray_manage', 'stray_manage');
	add_submenu_page(__FILE__, __('Add New Quote','stray-quotes'), __('Add New','stray-quotes'), 8, 'stray_new', 'stray_new');
	add_submenu_page(__FILE__, __('Settings of the Quotes','stray-quotes'), __('Settings','stray-quotes'), 8, 'stray_quotes_options', 'stray_quotes_options'); 
	add_submenu_page(__FILE__, __('Tools for your quotes','stray-quotes'), __('Tools','stray-quotes'), 8, 'stray_tools', 'stray_tools');
	add_submenu_page(__FILE__, __('Help with the Quotes','stray-quotes'), __('Help','stray-quotes'), 8, 'stray_help', 'stray_help');
	add_submenu_page(__FILE__, __('Remove Stray Random Quotes','stray-quotes'), __('Remove','stray-quotes'), 8, 'stray_remove', 'stray_remove'); 	
	
}

//excuse me, I'm hooking wordpress
add_action('admin_menu', 'stray_quotes_add_pages');
add_action('wp_print_scripts', 'stray_quotes_add_js');
add_action('admin_head', 'stray_quotes_header');

if ($wp_version >= 2.5) {
	add_shortcode('quote', 'stray_id_shortcut');
	add_shortcode('random-quote', 'stray_rnd_shortcut');
	add_shortcode('all-quotes', 'stray_page_shortcut');
}
register_activation_hook(__FILE__, 'quotes_activation');
register_deactivation_hook(__FILE__, 'quotes_deactivation');

$quotesoptions = get_option('stray_quotes_options');
if ($quotesoptions['comment_scode'] == 'Y') add_filter('comment_text', 'do_shortcode');
if ($quotesoptions['title_scode'] == 'Y') add_filter('the_title', 'do_shortcode');
if ($quotesoptions['excerpt_scode'] == 'Y') add_filter('the_excerpt', 'do_shortcode');
if ($quotesoptions['widget_scode'] == 'Y') add_filter('widget_text', 'do_shortcode');
if ($quotesoptions['categories_scode'] == 'Y') add_filter('the_category', 'do_shortcode');
if ($quotesoptions['tags_scode'] == 'Y') add_filter('the_tags', 'do_shortcode');
if ($quotesoptions['bloginfo_scode'] == 'Y') {add_filter('bloginfo', 'do_shortcode');add_filter('bloginfo_rss', 'do_shortcode');}

?>