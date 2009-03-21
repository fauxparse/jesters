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

