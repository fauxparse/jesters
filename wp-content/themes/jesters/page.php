<?php
/**
 * @package WordPress
 * @subpackage Jesters
 */
?>

<?php get_header(); ?>

<?php if (have_posts()) : ?>
  <?php while (have_posts()) : the_post(); ?>
    <div class="page span-24">
      <?php include "section_header.php" ?>
      <?php get_sidebar(); ?>
      <div id="main" class="span-16 last">
        <div id="main-inner">
          <?php
            if ($subsection) {
              $tertiary_post_list = wp_list_pages("title_li=&child_of={$subsection->ID}&depth=1&echo=0");
              if ($tertiary_post_list != "") {
                echo "<div class=\"tertiary navigation\"><ul><li class=\"first".($post->ID == $subsection->ID ? ' current_page_item' : '')."\"><a href=\"/{$root->post_name}/{$subsection->post_name}\">Overview</a></li>{$tertiary_post_list}</ul></div>";
              }
            }
          ?>
          <?php the_content(); ?>
        </div>
      </div>
    </div>
  <?php endwhile; ?>  
<?php else : ?>
	<h2 class="center">Not Found</h2>
	<p class="center">Sorry, but you are looking for something that isn't here.</p>
<?php endif; ?>

<?php get_footer(); ?>
