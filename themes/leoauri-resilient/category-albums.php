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

<?php get_template_part('parts/navigation'); ?>

<main>
<div id="album-display">
<?php
$albumsquery = new \WP_Query(array(
  'category_name' => 'albums',
  'posts_per_page' => -1,
  'orderby' => 'menu_order date',
));
while ($albumsquery->have_posts()):
$albumsquery->the_post();

$albumid = get_post_custom_values('albumid')[0];
if ($albumid) {
  $albumlink = get_post_custom_values('albumlink')[0];
  $albumalt = get_post_custom_values('albumalt')[0];

  ob_start();
  require('parts/mini-album-embed.php');
  echo ob_get_clean();
}
else {
  echo get_post_custom_values('albumembed')[0];
}

endwhile;
?>
</div>
</main>

<?php
get_footer();
