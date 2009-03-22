<?php
/*
Plugin Name: Rogues Gallery
Plugin URI: http://robo.tk
Description: Display a collection of users
Version: 0.1
Author: Matt Powell
Author URI: http://robo.tk
*/

function rogue_headshots() {
	global $wpdb, $blog_id;
  $users = array();
  $results = $wpdb->get_results( "SELECT * FROM {$wpdb->users} JOIN {$wpdb->usermeta} ON {$wpdb->usermeta}.user_id = {$wpdb->users}.ID WHERE {$wpdb->users}.user_login <> 'admin' ORDER BY {$wpdb->users}.display_name ASC" );
  foreach ($results as $result) {
    if (!isset($users[$result->ID])) {
      $users[$result->ID] = $result;
    }
    $users[$result->ID]->{$result->meta_key} = $result->meta_value;
  }
  usort($users, compare_users);
  $output = '<div id="headshots" class="span-16">';
  $i = 0;
  foreach ($users as $user) {
    $output .= "<div class=\"user span-4";
    if ($i % 4 == 3) {
      $output .= " last";
    }
    $output .= "\" id=\"user_{$user->ID}\"><a href=\"/about/us/{$user->user_nicename}\" title=\"{$user->display_name}\">";
    $portrait_url = get_cimyFieldValue($user->ID, "HEADSHOT");
  	$output .= "<img src=\"".$portrait_url."\" alt=\"{$user->display_name}\" />";
    $output .= "<span class=\"name\">{$user->display_name}</span></a></div>";
    $i++;
  }
  $output .= '</div>';
  return $output;
}

function compare_users(&$a, &$b) {
  if (($i = strcasecmp($a->last_name, $b->last_name)) == 0) {
    return strcasecmp($a->first_name, $b->first_name);
  } else {
    return $i;
  }
}

add_shortcode('headshots', rogue_headshots);

?>