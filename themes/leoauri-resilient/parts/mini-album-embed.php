<?php
namespace leoauriResilient;
/**
 * leoauri Resilient
 **/

?>
<div>
<iframe src="http://bandcamp.com/EmbeddedPlayer/album=<?php echo $albumid; ?>/size=large/bgcol=ffffff/linkcol=EE3D00/minimal=true/transparent=true/" seamless>
<a href="<?php echo $albumlink; ?>">
<?php echo $albumalt; ?>
</a>
</iframe>
<a href="<?php the_permalink(); ?>">
about
</a>
</div>
