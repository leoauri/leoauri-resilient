<?php
namespace leoauriResilient;
/**
 * leoauri Resilient
 **/


?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="theme-color" content="#1E764C" />
<meta name="author" content="Leo Auri" />
<meta name="description" content="Homepage Leo Auri, musician and composer" />
<link rel="preload" href="<?php echo get_template_directory_uri(); ?>/js/navbar.js" as="script" />
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/main.css" />
<link rel="license" href="https://creativecommons.org/licenses/by-sa/4.0/" />

<?php wp_head(); ?>
</head>
<?php if (is_category()) : ?>
<body class="category-<?php echo get_the_category()[0]->slug; ?>">
<?php elseif (is_page()) : ?>
<body class="page-<?php echo get_post_field('post_name'); ?>">
<?php else : ?>
<body>
<?php endif; ?>
