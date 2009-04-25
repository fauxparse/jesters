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
    <div id="splash-photo" class="span-8"><div></div>&nbsp;</div>
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
						echo wptexturize(get_post_meta($page->ID, 'summary', true));
						echo " <a class=\"more\" href=\"/{$page->post_name}\">Read&nbsp;more&nbsp;»</a></p></div></div><img src=\"/theme/images/splash-{$page->post_name}.png\" /></li>";
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
    <div class="span-16 last">
			<ol class="posts">
				<?php
					global $post;
				 	$posts = get_posts('category=3');
					$i = 0;
				 	foreach ($posts as $post) :
						setup_postdata($post);
				?>
					<li>
						<h3><?php the_title_attribute(); ?></h3>
						<?php the_content(); ?>
					</li>
				<?php
						if (!is_stickied()) { $i++; }
						if ($i > 1) { break; }
					endforeach;
				?>
			</ol>
			<div class="cleaner">&nbsp;</div>
    </div>
  </div>
  
  <div id="additional-content" class="span-24">
    
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function() {
    $('#navigator .primary-link').click(function() {
      var section = $(this).closest('li').not('.active');
      if (section.length > 0) {
				var old_section = section.siblings('.active').removeClass('active');
				$('#splash-photo div').fadeOut('fast');
        section.addClass('active').find('.teaser').show('blind', 'fast', function() {
          $('#splash-photo div').css('background-image', 'url(' + section.find('img').attr('src') + ')').fadeIn();
          old_section.find('.teaser').hide('blind');
        });
        //section.siblings('.active').removeClass('active').find('.teaser').hide('blind');
        return false;
      } else {
        return true;
      }
    });
  });
</script>

<?php get_footer(); ?>
