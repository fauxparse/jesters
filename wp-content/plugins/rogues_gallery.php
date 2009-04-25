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
			$users[$result->ID]->kind = get_cimyFieldValue($result->ID, "KIND");
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
    $output .= "\" id=\"user_{$user->ID}\"><a href=\"/about/us?user={$user->user_login}\" title=\"{$user->display_name}\" rel=\"facebox\">";
    $portrait_url = get_cimyFieldValue($user->ID, "HEADSHOT");
  	$output .= "<img src=\"".$portrait_url."\" alt=\"{$user->display_name}\" />";
    $output .= "<span class=\"name\">{$user->display_name}</span><span class=\"kind\">{$user->kind}</span></a></div>";
    $i++;
  }
  //$output .= "<script type=\"text/javascript\">\njQuery(document).ready(function() { \$('a[rel*=facebox]').facebox(); })\n</script>";
  $output .= '</div>';
  return $output;
}

function compare_users(&$a, &$b) {
	if ($a->kind == $b->kind) {
	  if (($i = strcasecmp($a->last_name, $b->last_name)) == 0) {
	    return strcasecmp($a->first_name, $b->first_name);
	  } else {
	    return $i;
	  }
	} else {
		$kinds = array('Manager', 'Jester', 'Muso');
		return array_search($a->kind, $kinds) - array_search($b->kind, $kinds);
	}
}

function player_portrait($atts, $content = null) {
	global $wpdb, $blog_id;
	extract(shortcode_atts(array(
		'name' => '',
		'role' => 'player'
	), $atts));
	if (!empty($name)) {
		$results = $wpdb->get_results("SELECT * FROM {$wpdb->users} JOIN {$wpdb->usermeta} ON {$wpdb->usermeta}.user_id = {$wpdb->users}.ID WHERE {$wpdb->users}.user_login = '{$name}'" );
		if (!empty($results)) {
			$output = "<div class=\"player {$role}\">";
			$user = $results[0];
			foreach ($results as $result) {
				$user->{$result->meta_key} = $result->meta_value;
			}
			$output .= "<a href=\"/about/us?user={$user->user_login}\" title=\"{$user->display_name}\" rel=\"facebox\">";
			$portrait_url = get_cimyFieldValue($user->ID, "HEADSHOT");
			$output .= "<img src=\"".$portrait_url."\" alt=\"{$user->display_name}\" />";
			$output .= "<span class=\"name\">{$user->nickname}";
			if ($role != 'player') {
				$output .= " <strong class=\"role\">{$role}</strong>";
			}
			$output .="</span></a></div>";
			return $output;
		} else {
			return "[can't find {$name}]";
		}
	} else {
		return "[something went wrong]";
	}
}

add_shortcode('headshots', rogue_headshots);
add_shortcode('player', player_portrait);

?>