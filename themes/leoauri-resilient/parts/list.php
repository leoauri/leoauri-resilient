<?php
namespace leoauriResilient;
/**
 * leoauri Resilient
 **/


$shorttitle = get_post_custom_values('shorttitle')[0];
if (!$shorttitle) {
  $shorttitle = get_the_title();
}

?>

<li>
<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
<?php echo $shorttitle; ?>
</a>
</li>
