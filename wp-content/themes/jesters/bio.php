<?php
/**
 * Template Name: Bio
 * @package WordPress
 * @subpackage Jesters
 */
?>

<?php

if (isset($_REQUEST['user'])) {
  $results = $wpdb->get_results( "SELECT * FROM {$wpdb->users} JOIN {$wpdb->usermeta} ON {$wpdb->usermeta}.user_id = {$wpdb->users}.ID WHERE {$wpdb->users}.user_login = '{$_REQUEST['user']}' ORDER BY {$wpdb->users}.display_name ASC" );
  foreach ($results as $result) {
    if (!isset($users[$result->ID])) {
      $users[$result->ID] = $result;
    }
    $users[$result->ID]->{$result->meta_key} = $result->meta_value;
  }
  if (count($users) > 0) {
    $user = array_shift($users);
    $image = get_cimyFieldValue($user->ID, "IMAGE");
    
    $output = "<div class=\"bio\">";
    $output .= "<h1>{$user->display_name}</h1>";
    $output .= "<img class=\"full-length\" src=\"{$image}\" alt=\"{$user->display_name}\" />";
    $output .= "<div class=\"description\">".wptexturize(wpautop($user->description))."</div>";
    $output .= "<div class=\"cleaner\"></div>";
    $output .= "</div>";
    
    /* View in the standard page template unless loaded via AJAX */
    if ($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
      $output .= "<p><a href=\"/about/us\">&laquo; Meet the rest of the Jesters</a></p>";
      $custom_content = $output;
      include('page.php');
    } else {
      echo $output;
    }
  } else {
    ?>
    <h1>Oops!</h1>
    <p>Try <a href="/about/us">looking for a real person</a>?</p>
    <?php
  }
} else {
  include('page.php');
}

?>
