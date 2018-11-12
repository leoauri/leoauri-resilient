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

<main>
<?php
while (have_posts()):
the_post();
?>
<header>
<h1>
<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
</h1>
</header>
<?php the_content(); ?>
<?php
if (! is_preview()) {
  edit_post_link();
}
?>
<?php endwhile; ?>
<nav>
<?php posts_nav_link(); ?>
</nav>
</main>

<?php
get_footer();
