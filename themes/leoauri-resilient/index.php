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
<?php
while (have_posts()):
the_post();
get_template_part('parts/post');
endwhile;
?>
<nav>
<?php posts_nav_link(); ?>
</nav>
</main>

<?php
get_footer();
