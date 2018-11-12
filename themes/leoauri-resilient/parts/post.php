<?php
namespace leoauriResilient;
/**
 * leoauri Resilient
 **/


?>
<article>
<header>
<h1>
<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
</h1>
<?php if (! is_page()) : ?>
<time pubdate><?php echo get_the_date(); ?></time>
<?php endif; ?>
</header>
<?php the_content(); ?>
<?php if (! is_preview()) {
  edit_post_link();
} ?>
</article>
