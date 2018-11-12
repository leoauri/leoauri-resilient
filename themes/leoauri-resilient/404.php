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
<h1>404</h1>
<p>Unfortunately, nothing was found at that URL.</p>
<?php
// Get the URI minus beginning slash and replacing with spaces to use as search
$request = str_replace(str_split('/.?=+'), ' ', substr($_SERVER['REQUEST_URI'], 1));
// echo $request;
$search = new \WP_Query(array(
  's' => $request,
  'posts_per_page' => 10,
));
// var_dump($search);
?>
<p>Perhaps you can find what you were looking for via the site navigation (☰).
<?php if ($search->have_posts()) : ?>
Otherwise, the following related content may help you:
<?php endif; ?>
</p>
<?php
while ($search->have_posts()) :
$search->the_post();
get_template_part('parts/excerpt');
endwhile;
?>
</main>

<?php
get_footer();
