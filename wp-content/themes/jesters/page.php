<?php
/**
 * @package WordPress
 * @subpackage Jesters
 */
?>

<?php get_header(); ?>

<?php if (have_posts()) : ?>
  <?php while (have_posts()) : the_post(); ?>
    <?php
      $root = $post;
      while ($root->post_parent != "0") {
        $root = get_posts('post_type=page&include='.$root->post_parent);
        $root = $root[0];
      }
      
      $subsection = $post;
      while ($subsection->post_parent != "0" && $subsection->post_parent != $root->ID) {
        $subsection = get_posts('post_type=page&include='.$subsection->post_parent);
        $subsection = $subsection[0];
      }
      if ($subsection->post_parent == "0") {
        $subsection = null;
      }
    ?>
    
    <div class="page span-24">
      <div id="header">
        <a id="logo" href="/">The Court Jesters</a>
        <div class="primary navigation">
          <ul>
            <?php wp_list_pages('depth=1&title_li='); ?>
          </ul>
        </div>
        <div class="secondary navigation">
          <ul>
            <li class="first"><?php echo "<a href=\"/{$root->post_name}\">{$root->post_title}</a>" ?></li>
            <?php wp_list_pages("title_li=&child_of={$root->ID}&depth=1"); ?>
          </ul>
        </div>
        <?php  ?>
        <h1><?php the_title_attribute(); ?>
      </div>
      <div id="sub-header" class="span-24">
        <div id="testimonial" class="span-8">
          <?php if (function_exists('stray_random_quote')) stray_random_quote('testimonials', false, '', false, 1, 0, 'quoteID', 'ASC'); ?>
        </div>
        <div id="intro" class="span-16 last">
          <?php
            if ($subsection) {
              $tertiary_post_list = wp_list_pages("title_li=&child_of={$subsection->ID}&depth=1&echo=0");
              if ($tertiary_post_list != "") {
                echo "<div class=\"tertiary navigation\"><ul><li class=\"first".($post->ID == $subsection->ID ? ' current_page_item' : '')."\"><a href=\"/{$root->post_name}/{$subsection->post_name}\">Overview</a></li>{$tertiary_post_list}</ul></div>";
              }
            }
          ?>
        </div>
      </div>
      
      <!-- Start content -->
      <?php the_content(); ?>
      <!-- End content -->
    </div>
	<?php endwhile; ?>
<?php else : ?>
	<h2 class="center">Not Found</h2>
	<p class="center">Sorry, but you are looking for something that isn't here.</p>
<?php endif; ?>

<?php get_footer(); ?>
