<?php
/**
 * Template Name: Homepage
 * @package WordPress
 * @subpackage Jesters
 */
?>

<?php get_header(); ?>

<div id="home">
  <div id="header">
    <a id="logo" href="/">The Court Jesters</a>
    <?php if (function_exists('stray_random_quote')) stray_random_quote('testimonials', false, '', false, 1, 0, 'quoteID', 'ASC'); ?>
  </div>

  <div id="navigator" class="span-24">
    <div id="main-photo" class="span-8">&nbsp;</div>
    <div id="tabs" class="span-16 last">
      <ul>
				<?php

				$pages = get_pages(array('depth' => 0, 'child_of' => 0, 'exclude' => '', 'sort_column' => 'menu_order, post_title'));

				$i = 0;
				foreach ($pages as $page) {
					if ($page->post_name != 'home' && $page->post_parent == "0") {
						echo "<li";
						if ($i == 0) {
							echo " class=\"active\"";
						}
						echo "><a class=\"primary-link\" href=\"/{$page->post_name}\">{$page->post_title}</a><ul class=\"secondary-links\">";
            wp_list_pages("title_li=&child_of={$page->ID}&depth=1&limit=3");
            echo get_post_meta($page->ID, 'extra_links', true);
						echo "</ul><div class=\"details\"><div class=\"teaser\"";
						if ($i > 0) {
							echo " style=\"display: none;\"";
						}
						echo "><p>";
						echo get_post_meta($page->ID, 'summary', true);
						echo " <a class=\"more\" href=\"/{$page->post_name}\">Read more »</a></p></div></div></li>";
						$i++;
					}
				}
				?>
      </ul>
    </div>
  </div>

  <div id="whats-on" class="span-24">
    <div class="span-8">
      <h2>What’s on?</h2>
    </div>
    <div class="span-16">
      
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function() {
    $('#navigator .primary-link').click(function() {
      var section = $(this).closest('li').not('.active');
      if (section.length > 0) {
				var old_section = section.siblings('.active').removeClass('active');
        section.addClass('active').find('.teaser').show('blind', 'fast', function() { old_section.find('.teaser').hide('blind'); });
        //section.siblings('.active').removeClass('active').find('.teaser').hide('blind');
      }
      return false;
    });
  });
</script>

<?php get_footer(); ?>
