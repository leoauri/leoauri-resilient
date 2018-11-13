<?php
namespace leoauriResilient;
/**
 * leoauri Resilient
 **/


get_header(); ?>

<header>
<hgroup>
<h1><?php bloginfo('name'); ?></h1>
<h2><?php bloginfo('description'); ?></h2>
</hgroup>
</header>

<?php get_template_part('parts/navigation'); ?>

<main>

<?php
the_post();
the_content();
// $intro = get_post(get_page_by_title('introduction'));
// echo apply_filters('the_content', $intro->post_content);
?>
</main>

<?php
get_footer();
