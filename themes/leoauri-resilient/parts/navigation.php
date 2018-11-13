<?php
namespace leoauriResilient;
/**
 * leoauri Resilient
 **/

wp_enqueue_script('navbar');

?>
<nav>
<a id="nav-on-switch" href="<?php echo get_permalink(get_page_by_path('navigation')); ?>">&#9776;</a>
<div id="nav-sidebar">
<a id="nav-off-switch" href="#">&#x2573;</a>
<?php wp_nav_menu(array(
  'menu' => '',
  'container' => '',
  'container_class' => '',
  'container_id' => '',
  'menu_class' => '',
  'menu_id' => '',
  'echo' => true,
  'fallback_cb' => '',
  'before' => '',
  'after' => '',
  'link_before' => '',
  'link_after' => '',
  'items_wrap' => '<ul>%3$s</ul>',
  'item_spacing' => 'discard',
  'depth' => 0,
  'walker' => '',
  'theme_location' => 'navigation'
)); ?>
</div>
</nav>
