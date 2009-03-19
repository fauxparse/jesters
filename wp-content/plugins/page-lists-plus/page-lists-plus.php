<?php
/*
Plugin Name: Page Lists Plus
Plugin URI: http://www.technokinetics.com/plugins/page-lists-plus/
Description: Adds customisation options to the wp_list_pages function which is used to create Page menus. Change the link text and title attributes, redirect, nofollow or unlink links, and remove links from page lists altogether, all through the WordPress dashboard.
Version: 0.1.7
Author: Tim Holt
Author URI: http://www.technokinetics.com/

    Copyright 2008 Tim Holt (tim@technokinetics.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// HOOKS

register_activation_hook(__FILE__,'page_lists_plus_install');
register_deactivation_hook( __FILE__, 'page_lists_plus_uninstall' );
add_action('admin_menu', 'page_lists_plus_add_fields');
add_action('save_post', 'save_page_lists_plus');
add_filter('wp_list_pages_excludes', 'page_exclusions');
add_filter('wp_list_pages', 'page_lists_plus');
add_action('admin_menu', 'add_page_lists_plus_admin_menu');



// ACTIVATION

function page_lists_plus_install() {
	global $wpdb;
	$posts_table = $wpdb->prefix . 'posts';
	mysql_query("ALTER TABLE " . $posts_table . " ADD show_in_menu TINYINT(1) DEFAULT 1 NOT NULL AFTER post_title");
	mysql_query("ALTER TABLE " . $posts_table . " ADD link_link TINYINT(1) DEFAULT 1 NOT NULL AFTER show_in_menu");
	mysql_query("ALTER TABLE " . $posts_table . " ADD no_follow_link TINYINT(1) DEFAULT 0 NOT NULL AFTER link_link");
	mysql_query("ALTER TABLE " . $posts_table . " ADD alt_link_text VARCHAR(100) AFTER no_follow_link");
	mysql_query("ALTER TABLE " . $posts_table . " ADD redirect_url VARCHAR(100) AFTER alt_link_text");
	mysql_query("ALTER TABLE " . $posts_table . " ADD target_blank TINYINT(1) DEFAULT 0 NOT NULL AFTER redirect_url");
	mysql_query("ALTER TABLE " . $posts_table . " ADD alt_title_attribute VARCHAR(100) AFTER target_blank");
	add_option('plp_show_link_text_field', 'on');
	add_option('plp_show_link_field', 'on');
}



// DEACTIVATION

function page_lists_plus_uninstall() {

	if (get_option('plp_delete_data_on_deactivation') == 'on') {
		global $wpdb;
		$posts_table = $wpdb->prefix . 'posts';
		
		mysql_query("ALTER TABLE " . $posts_table . " DROP show_in_menu");
		mysql_query("ALTER TABLE " . $posts_table . " DROP link_link");
		mysql_query("ALTER TABLE " . $posts_table . " DROP no_follow_link");
		mysql_query("ALTER TABLE " . $posts_table . " DROP alt_link_text");
		mysql_query("ALTER TABLE " . $posts_table . " DROP redirect_url");
		mysql_query("ALTER TABLE " . $posts_table . " DROP target_blank");
		mysql_query("ALTER TABLE " . $posts_table . " DROP alt_title_attribute");
		
		delete_option('plp_add_home_link');
		delete_option('plp_add_contact_link');
		delete_option('plp_label_first_item');
		delete_option('plp_add_spans_at_start_of_list_items');
		delete_option('plp_add_spans_inside_list_items');
		delete_option('plp_add_spans_at_start_of_anchors');
		delete_option('plp_add_spans_inside_anchors');
		delete_option('plp_remove_title_attributes');
		
		delete_option('plp_show_link_text_field');
		delete_option('plp_show_title_attribute_field');
		delete_option('plp_show_redirect_field');
		delete_option('plp_show_target_blank');
		delete_option('plp_show_include_field');
		delete_option('plp_show_link_field"');
		delete_option('plp_show_nofollow_field');
		
		delete_option('plp_delete_data_on_deactivation');
	}
}



// ADMIN MENU
function add_page_lists_plus_admin_menu() {
	add_options_page('Page Lists Plus', 'Page Lists Plus', 9, __FILE__, 'page_lists_plus_admin_menu');
}

function page_lists_plus_admin_menu() { ?>
	<div id="page_lists_plus" class="wrap">
		<h2>Page Lists Plus</h2>
		<form method="post" action="options.php">
			<h3>Global Options</h3>
			<p>These options aren't specific to individual Pages; instead they let you modify your entire Page lists.</p>
			<ul style="list-style: none;">
				<li><input type="checkbox" id="plp_add_home_link" name="plp_add_home_link" <?php if (get_option('plp_add_home_link') == "on") { echo 'checked="checked" '; } ?>/> <label for="plp_add_home_link">Add "Home" link at the start of Page lists</label></li>
				<li><input type="checkbox" id="plp_add_contact_link" name="plp_add_contact_link" <?php if (get_option('plp_add_contact_link') == "on") { echo 'checked="checked" '; } ?>/> <label for="plp_add_contact_link">Add "Contact" link at the end of Page lists</label></li>
				<li><input type="checkbox" id="plp_label_first_item" name="plp_label_first_item" <?php if (get_option('plp_label_first_item') == "on") { echo 'checked="checked" '; } ?>/> <label for="plp_label_first_item">Add class="first_item" to first list item (this can be useful if you want to style your first link differently to others, such as in horizontal menus with separators between Page links)</label></li>
				<li><input type="checkbox" id="plp_add_spans_at_start_of_list_items" name="plp_add_spans_at_start_of_list_items" <?php if (get_option('plp_add_spans_at_start_of_list_items') == "on") { echo 'checked="checked" '; } ?>/> <label for="plp_add_spans_at_start_of_list_items">Add span tags at start of list items, i.e. <?php echo htmlentities('<li><span></span><a href="#">Link</a></li>'); ?></label></li>
				<li><input type="checkbox" id="plp_add_spans_inside_list_items" name="plp_add_spans_inside_list_items" <?php if (get_option('plp_add_spans_inside_list_items') == "on") { echo 'checked="checked" '; } ?>/> <label for="plp_add_spans_inside_list_items">Add span tags inside list items, i.e. <?php echo htmlentities('<li><span><a href="#">Link</a></span></li>'); ?></label></li>
				<li><input type="checkbox" id="plp_add_spans_at_start_of_anchors" name="plp_add_spans_at_start_of_anchors" <?php if (get_option('plp_add_spans_at_start_of_anchors') == "on") { echo 'checked="checked" '; } ?>/> <label for="plp_add_spans_at_start_of_anchors">Add span tags at start of anchors, i.e. <?php echo htmlentities('<li><a href="#"><span></span>Link</a></li>'); ?></label></li>
				<li><input type="checkbox" id="plp_add_spans_inside_anchors" name="plp_add_spans_inside_anchors" <?php if (get_option('plp_add_spans_inside_anchors') == "on") { echo 'checked="checked" '; } ?>/> <label for="plp_add_spans_inside_anchors">Add span tags inside anchors, i.e. <?php echo htmlentities('<li><a href="#"><span>Link</span></a></li>'); ?></label></li>
				<li><input type="checkbox" id="plp_remove_title_attributes" name="plp_remove_title_attributes" <?php if (get_option('plp_remove_title_attributes') == "on") { echo 'checked="checked" '; } ?>/> <label for="plp_remove_title_attributes">Remove title attributes from anchors</label></li>
			</ul>
			<h3>Page-Specific Options</h3>
			<p>Page Lists Plus includes lots of options, and people use it in lots of different ways. The chances are that there are some features of Page Lists Plus that aren't relevant to you. Rather than clutter up your Edit screens by having all of the options showing all of the time, you can choose which options you want to see by checking or unchecking them below.</p>
			<p>So, which options would you like to appear on your Write > Page and Manage > Page screens?</p>
			<ul style="list-style: none;">
				<li><input type="checkbox" id="plp_show_link_text_field" name="plp_show_link_text_field" <?php if (get_option('plp_show_link_text_field') == "on") { echo 'checked="checked" '; } ?>/> <label for="plp_show_link_text_field">Alternative Link Text (lets you specify alternative link text to be used in Page lists instead of the Page title)</label></li>
				<li><input type="checkbox" id="plp_show_title_attribute_field" name="plp_show_title_attribute_field" <?php if (get_option('plp_show_title_attribute_field') == "on") { echo 'checked="checked" '; } ?>/> <label for="plp_show_title_attribute_field">Alternative Title Attribute (lets you specify an alternative title attribute to be used in Page lists instead of the Page title)</label></li>
				<li><input type="checkbox" id="plp_show_redirect_field" name="plp_show_redirect_field" <?php if (get_option('plp_show_redirect_field') == "on") { echo 'checked="checked" '; } ?>/> <label for="plp_show_redirect_field">Redirect To (lets you redirect the link to a different url)</label></li>
				<li><input type="checkbox" id="plp_show_target_blank" name="plp_show_target_blank" <?php if (get_option('plp_show_target_blank') == "on") { echo 'checked="checked" '; } ?>/> <label for="plp_show_target_blank">Open link in new window</label></li>
				<li><input type="checkbox" id="plp_show_include_field" name="plp_show_include_field" <?php if (get_option('plp_show_include_field') == "on") { echo 'checked="checked" '; } ?>/> <label for="plp_show_include_field">Include (lets you specify which Pages appear in Page lists and which don't)</label></li>
				<li><input type="checkbox" id="plp_show_link_field" name="plp_show_link_field" <?php if (get_option('plp_show_link_field') == "on") { echo 'checked="checked" '; } ?>/> <label for="plp_show_link_field">Link (lets you unlink Pages without removing them from your Page lists)</label></li>
				<li><input type="checkbox" id="plp_show_nofollow_field" name="plp_show_nofollow_field" <?php if (get_option('plp_show_nofollow_field') == "on") { echo 'checked="checked" '; } ?>/> <label for="plp_show_nofollow_field">Nofollow (lets you add rel="nofollow" to links, telling search engines not to follow them)</label></li>
			</ul>			
			<p>N.B. Modifying these options just changes what options you see on your Edit screens; it doesn't change or remove any data. So if a Page is included or excluded from your Page lists and you remove the "Include" option from your Edit screens, the Page will still be included or excluded, just as it was before.</p>
			<h3>Plugin Options</h3>
			<ul style="list-style: none;">
				<li><input type="checkbox" id="plp_delete_data_on_deactivation" name="plp_delete_data_on_deactivation" <?php if (get_option('plp_delete_data_on_deactivation') == "on") { echo 'checked="checked" '; } ?>/> <label for="plp_delete_data_on_deactivation">Delete plugin data on deactivation</label></li>
			</ul>
			<h3>Feedback</h3>
			<p>If you've found Page Lists Plus useful, then please consider <a href="http://wordpress.org/extend/plugins/page-lists-plus/">rating it</a>, linking to <a href="http://www.technokinetics.com/">my website</a>, or <a href="http://www.technokinetics.com/donations/">making a donation</a>.</p>
			<p>If you haven't found it useful, then please consider <a href="mailto:tim@technokinetics.com?subject=PLP Bug Report">filing a bug report</a> or <a href="mailto:tim@technokinetics.com?subject=PLP Feature Request">making a feature request</a>.</p>
			<p>Thanks!</p>
			<p>- Tim Holt, <a href="http://www.technokinetics.com/">Technokinetics</a></p>
			<p>
				<?php wp_nonce_field('update-options'); ?>
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="page_options" value="plp_add_home_link,plp_add_contact_link,plp_label_first_item,plp_add_spans_at_start_of_list_items,plp_add_spans_inside_list_items,plp_add_spans_at_start_of_anchors,plp_add_spans_inside_anchors,plp_remove_title_attributes,plp_show_link_text_field,plp_show_title_attribute_field,plp_show_redirect_field,plp_show_target_blank,plp_show_include_field,plp_show_link_field,plp_show_nofollow_field,plp_delete_data_on_deactivation" />
			</p>
			<p class="submit"><input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" /></p>
		</form>		
	</div><?php
}

// ADD FIELD

function page_lists_plus_add_fields() {
	if (function_exists('add_meta_box')) {
		add_meta_box('page_lists_plus_box', 'Page Lists Plus', 'page_lists_plus_inner', 'page', 'normal', 'low');
	}
}

function page_lists_plus_inner() {
	global $post, $show_in_menu, $alt_link_text, $alt_title_attribute, $custom_link_class; ?>
	
	<?php if (get_option('plp_show_link_text_field') == 'on') { ?>
		<p><label for="alt_link_text">Alternative Link Text</label><br/><input type="text" id="alt_link_text" name="alt_link_text" value="<?php echo $post->alt_link_text; ?>" /> This link text will be used in page lists generated using wp_list_pages().</p><?php
	} ?>
	
	<?php if (get_option('plp_show_title_attribute_field') == 'on') { ?>
		<p><label for="alt_title_attribute">Alternative Title Attribute</label><br/><input type="text" id="alt_title_attribute" name="alt_title_attribute" value="<?php echo $post->alt_title_attribute; ?>" /> This title attribute will be used in page lists generated using wp_list_pages().</p><?php
	} ?>
	
	<?php if (get_option('plp_show_redirect_field') == 'on') { ?>
		<p><label for="redirect_url">Redirect To</label><br/><input type="text" id="redirect_url" name="redirect_url" value="<?php echo $post->redirect_url; ?>" /> This url will be used in lists generated using wp_list_pages as this Page's link's destination.</p><?php
	} ?>
	
	<?php if (get_option('plp_show_target_blank') == 'on') { ?>
		<p><label for="target_blank">Target Blank</label><br/><input type="checkbox" id="target_blank" name="target_blank" <?php if (!isset($post->target_blank) || $post->target_blank == 1) { echo 'checked="checked" '; } ?>/> If this box is checked, then this link will open a new browser window.</p><?php
	} ?>
	
	<?php if (get_option('plp_show_include_field') == 'on') { ?>
		<p><label for="show_in_menu">Include in Page Lists</label><br/><input type="checkbox" id="show_in_menu" name="show_in_menu" <?php if (!isset($post->show_in_menu) || $post->show_in_menu == 1) { echo 'checked="checked" '; } ?>/> If this box is checked, then this Page will appear in page lists generated using wp_list_pages().</p><?php
	} ?>
	
	<?php if (get_option('plp_show_link_field') == 'on') { ?>
		<p><label for="link_link">Link</label><br/><input type="checkbox" id="link_link" name="link_link" <?php if (!isset($post->show_in_menu) || $post->link_link == 1) { echo 'checked="checked" '; } ?>/> If this box is checked, then this Page will be linked in lists generated using wp_list_pages().</p><?php
	} ?>
	
	<?php if (get_option('plp_show_nofollow_field') == 'on') { ?>
		<p><label for="no_follow_link">Nofollow</label><br/><input type="checkbox" id="no_follow_link" name="no_follow_link" <?php if ($post->no_follow_link == 1) { echo 'checked="checked" '; } ?>/> If this box is checked, then links to this Page in lists generated using wp_list_pages() will have rel="nofollow" added.</p><?php
	} ?>
	
	<p>You can change which options appear here through the <a href="<?php bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=page-lists-plus/page-lists-plus.php">Settings > Page Lists Plus</a> screen.</p>
	
	<p><input type="hidden" name="manual_save" value="manual_save" /></p><?php
}

// SAVE DATA

function save_page_lists_plus() {

	global $wpdb;
	$posts_table = $wpdb->prefix . 'posts';
	
	if ($_POST[manual_save] == 'manual_save') {
	
		if ($_POST[alt_link_text] == "") {
			mysql_query("UPDATE " . $posts_table . " SET alt_link_text = null WHERE ID = $_POST[ID]");
		} elseif (get_option('plp_show_link_text_field') == 'on') {
			mysql_query("UPDATE " . $posts_table . " SET alt_link_text = '" . $_POST[alt_link_text] . "' WHERE ID = $_POST[ID]");
		}
		
		if ($_POST[alt_title_attribute] == "") {
			mysql_query("UPDATE " . $posts_table . " SET alt_title_attribute = null WHERE ID = $_POST[ID]");
		} elseif (get_option('plp_show_title_attribute_field') == 'on') {
			mysql_query("UPDATE " . $posts_table . " SET alt_title_attribute = '" . $_POST[alt_title_attribute] . "' WHERE ID = $_POST[ID]");
		}
		
		if ($_POST[redirect_url] == "") {
			mysql_query("UPDATE " . $posts_table . " SET redirect_url = null WHERE ID = $_POST[ID]");
		} elseif (get_option('plp_show_redirect_field') == 'on') {
			mysql_query("UPDATE " . $posts_table . " SET redirect_url = '" . $_POST[redirect_url] . "' WHERE ID = $_POST[ID]");
		}
		
		if ($_POST[target_blank]) {
			mysql_query("UPDATE " . $posts_table . " SET target_blank = '1' WHERE ID = $_POST[ID]");
		} elseif (get_option('plp_show_target_blank') == 'on') {
			mysql_query("UPDATE " . $posts_table . " SET target_blank = '0' WHERE ID = $_POST[ID]");
		}
		
		if ($_POST[show_in_menu]) {
			mysql_query("UPDATE " . $posts_table . " SET show_in_menu = '1' WHERE ID = $_POST[ID]");
		} elseif (get_option('plp_show_include_field') == 'on') {
			mysql_query("UPDATE " . $posts_table . " SET show_in_menu = '0' WHERE ID = $_POST[ID]");
		}
		
		if ($_POST[link_link]) {
			mysql_query("UPDATE " . $posts_table . " SET link_link = '1' WHERE ID = $_POST[ID]");
		} elseif (get_option('plp_show_link_field') == 'on') {
			mysql_query("UPDATE " . $posts_table . " SET link_link = '0' WHERE ID = $_POST[ID]");
		}
		
		if ($_POST[no_follow_link]) {
			mysql_query("UPDATE " . $posts_table . " SET no_follow_link = '1' WHERE ID = $_POST[ID]");
		} elseif (get_option('plp_show_nofollow_field') == 'on') {
			mysql_query("UPDATE " . $posts_table . " SET no_follow_link = '0' WHERE ID = $_POST[ID]");
		}
	
	}
	
}



// EXCLUDE PAGES FROM PAGE LIST

function page_exclusions($page_exclusions) {
	global $wpdb;
	$posts_table = $wpdb->prefix . 'posts';
	$page_exlusions_data = mysql_query("SELECT ID FROM " . $posts_table . " WHERE show_in_menu = '0' AND post_status = 'publish'");
	while ($row = mysql_fetch_assoc($page_exlusions_data)) {
		extract($row);
		$page_exclusions[] = $ID;
	}
	return $page_exclusions;
}



// REPLACEMENTS IN WP-LIST-PAGES RESULTS

function page_lists_plus($output) {	
	global $wpdb;
	$posts_table = $wpdb->prefix . 'posts';
	
	if (get_option('plp_add_home_link') == 'on') {
		// If wp_list_pages is called with a title, $output will be wrapped in a <li> with the class "pagenav" and the page list will wrapped in <ul> tags, so if we find that list item then we'll insert the Home link after the first <ul>.
		if (stristr($output, '<li class="pagenav">') != FALSE) {
			$output = preg_replace('`<ul>`', '<ul><li class="page_item"><a href="' . get_bloginfo('home') . '">Home</a></li>', $output, 1);
		// If wp_list_pages is called without a title, then it won't be wrapped in a <li> with the class "pagenav" and the page list won't be wrapped in <ul> tags, so if we don't find that list item we'll insert the Home link after the first <li>.
		} else {
			$output = preg_replace('`<li`', '<li class="page_item"><a href="' . get_bloginfo('home') . '">Home</a></li><li', $output, 1);
		}
	}
	
	if (get_option('plp_add_contact_link') == 'on') {
		// If wp_list_pages is called with a title, $output will be wrapped in a <li> with the class "pagenav" and the page list will wrapped in <ul> tags, so if we find that list item then we'll insert the Contact link before the last <ul>.
		if (stristr($output, '<li class="pagenav">') != FALSE) {
			$output = substr_replace($output, '<li class="page_item"><a href="mailto:' . get_bloginfo('admin_email') . '">Contact</a></li></ul>', strrpos($output, '</ul>')); 
		// If wp_list_pages is called without a title, then it won't be wrapped in a <li> with the class "pagenav" and the page list won't be wrapped in <ul> tags, so if we don't find that list item we'll just tack the Contact on at the end.
		} else {
			$output .= '<li class="page_item"><a href="mailto:' . get_bloginfo('admin_email') . '">Contact</a></li>';
		}
	}
	
	if (get_option('plp_label_first_item') == 'on') {
		$output = preg_replace('`class="page_item`', 'class="first_item page_item', $output, 1);
	}
	
	if (get_option('plp_remove_title_attributes') == 'on') {
		$output = preg_replace('` title="(.+)"`', '', $output);
	}
	
	$alt_title_attribute_data = mysql_query("SELECT post_title, alt_title_attribute FROM " . $posts_table . " WHERE alt_title_attribute IS NOT NULL AND post_status = 'publish'");
	while ($row = mysql_fetch_assoc($alt_title_attribute_data)) {
		extract($row);
		$post_title = wptexturize($post_title);
		if (get_option('plp_remove_title_attributes') == 'on') {
			$output = str_replace('>' . $post_title . '<', ' title="' . $alt_title_attribute . '">' . $post_title . '<', $output);
		} else {
			$output = str_replace('title="' . $post_title . '"', 'title="' . $alt_title_attribute . '"', $output);
		}
	}
	
	$no_follow_link_data = mysql_query("SELECT post_title, no_follow_link FROM " . $posts_table . " WHERE no_follow_link = '1' AND post_status = 'publish'");
	while ($row = mysql_fetch_assoc($no_follow_link_data)) {
		extract($row);
		$post_title = wptexturize($post_title);
		$output = str_replace('>' . $post_title . '<', ' rel="nofollow">' . $post_title . '<', $output);
	}
	
	$target_blank_data = mysql_query("SELECT post_title, target_blank FROM " . $posts_table . " WHERE target_blank = '1' AND post_status = 'publish'");
	while ($row = mysql_fetch_assoc($target_blank_data)) {
		extract($row);
		$post_title = wptexturize($post_title);
		$output = str_replace('>' . $post_title . '<', ' target="_blank">' . $post_title . '<', $output);
	}
	
	$link_link_data = mysql_query("SELECT post_title FROM " . $posts_table . " WHERE link_link = '0' AND post_status = 'publish'");
	while ($row = mysql_fetch_assoc($link_link_data)) {
		extract($row);
		$post_title = wptexturize($post_title);
		$output = preg_replace('`<a(.+)' . $post_title . '(.+)+/a>`', $post_title, $output);
	}
	
	if (get_option('plp_add_spans_at_start_of_list_items') == 'on') {	
		// Add spans to linked Pages
		$output = preg_replace('`<a(.+)</a>`', '<span></span><a$1</a>', $output);
		
		// Add spans to unlinked Pages
		$link_link_data = mysql_query("SELECT post_title FROM " . $posts_table . " WHERE link_link = '0' AND post_status = 'publish'");
		while ($row = mysql_fetch_assoc($link_link_data)) {
			extract($row);
			$post_title = wptexturize($post_title);
			$output = str_replace($post_title, '<span></span>' . $post_title, $output);
		}
	}
	
	if (get_option('plp_add_spans_inside_list_items') == 'on') {	
		// Add spans to linked Pages
		$output = preg_replace('`<a(.+)</a>`', '<span><a$1</a></span>', $output);
		
		// Add spans to unlinked Pages
		$link_link_data = mysql_query("SELECT post_title FROM " . $posts_table . " WHERE link_link = '0' AND post_status = 'publish'");
		while ($row = mysql_fetch_assoc($link_link_data)) {
			extract($row);
			$post_title = wptexturize($post_title);
			$output = str_replace($post_title, '<span>' . $post_title . '</span>', $output);
		}
	}
	
	if (get_option('plp_add_spans_at_start_of_anchors') == 'on') {	
		// Add spans to linked Pages
		$output = preg_replace('`<a(.+)">`', '<a$1"><span></span>', $output);
		
		// Add spans to unlinked Pages
		$link_link_data = mysql_query("SELECT post_title FROM " . $posts_table . " WHERE link_link = '0' AND post_status = 'publish'");
		while ($row = mysql_fetch_assoc($link_link_data)) {
			extract($row);
			$post_title = wptexturize($post_title);
			$output = str_replace($post_title, '<span></span>' . $post_title, $output);
		}
	}
	
	if (get_option('plp_add_spans_inside_anchors') == 'on') {	
		// Add spans to linked Pages
		$output = preg_replace('`<a(.+)</a>`', '<a$1</span></a>', $output);
		$output = preg_replace('`<a(.+)">`', '<a$1"><span>', $output);
		
		// Add spans to unlinked Pages
		$link_link_data = mysql_query("SELECT post_title FROM " . $posts_table . " WHERE link_link = '0' AND post_status = 'publish'");
		while ($row = mysql_fetch_assoc($link_link_data)) {
			extract($row);
			$post_title = wptexturize($post_title);
			$output = str_replace($post_title, '<span>' . $post_title . '</span>', $output);
		}
	}
	
	$alt_link_text_data = mysql_query("SELECT post_title, alt_link_text FROM " . $posts_table . " WHERE alt_link_text IS NOT NULL AND post_status = 'publish'");
	while ($row = mysql_fetch_assoc($alt_link_text_data)) {
		extract($row);
		$post_title = wptexturize($post_title);
		$output = str_replace('>' . $post_title . '<', '>' . $alt_link_text . '<', $output);
	}
	
	$redirect_url_data = mysql_query("SELECT ID, redirect_url FROM " . $posts_table . " WHERE redirect_url IS NOT NULL AND post_status = 'publish'");
	while ($row = mysql_fetch_assoc($redirect_url_data)) {
		extract($row);
		$page_url = get_permalink($ID);
		$output = str_replace($page_url . '"', $redirect_url . '"', $output);
	}
	
	return $output;
}

?>