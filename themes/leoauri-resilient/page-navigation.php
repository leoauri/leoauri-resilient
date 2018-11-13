<?php
namespace leoauriResilient;
/**
 * leoauri Resilient
 **/


get_header(); ?>

<header>
<a href="<?php echo esc_url(home_url('/')); ?>">
<?php bloginfo('name'); ?>
</a>
</header>

<nav>
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
</nav>

<?php
get_footer();
