<?php
namespace leoauriResilient;
/**
 * leoauri Resilient
 **/


?>
<article>
<header>
<h2>
<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
</h2>
<?php if (! is_page()) : ?>
<time pubdate><?php echo get_the_date(); ?></time>
<?php endif; ?>
</header>
<?php the_excerpt(); ?>
<a href="<?php the_permalink(); ?>">Complete article →</a>
</article>
