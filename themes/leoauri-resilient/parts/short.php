<?php
namespace leoauriResilient;
/**
 * leoauri Resilient
 **/

$shorttitle = get_post_custom_values('shorttitle')[0];

$frontpageversion = get_post_custom_values('frontpageversion')[0];
$frontpageversion = apply_filters('the_content', $frontpageversion);
if (!$frontpageversion) {
  $frontpageversion = get_the_excerpt();
}


?>

<section>
<?php echo $frontpageversion; ?>
<div>
<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
About <?php echo ($shorttitle) ? $shorttitle : 'this'; ?> &rarr;
</a>
</div>
</section>
