<?php
/**
 * @package WordPress
 * @subpackage Jesters
 */
?>

<div id="sidebar" class="span-8">
  <div id="sidebar-inner">
    <?php
      echo get_post_meta($post->ID, 'sidebar', true);
      if ($post->post_name != "testimonials") {
        if (function_exists('stray_random_quote')) stray_random_quote('testimonials', false, '', false, 1, 0, 'quoteID', 'ASC');
      }
    ?>
  </div>
</div>
