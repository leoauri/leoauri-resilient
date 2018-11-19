<?php
namespace leoauriResilient;
/**
 * leoauri Resilient
 **/

$trackID = get_post_custom_values('track-ID')[0];
$albumID = get_post_custom_values('album-ID')[0];
$fallbackLink = get_post_custom_values('fallback-link')[0];
$fallbackText = get_post_custom_values('fallback-text')[0];
$fromAlbumPost = get_post_custom_values('from-album-post')[0];


?>
<iframe style="border: 0; width: 100%; height: 120px;" src="https://bandcamp.com/EmbeddedPlayer/album=<?php echo $albumID; ?>/size=large/bgcol=ffffff/linkcol=EE3D00/tracklist=false/artwork=small/track=<?php echo $trackID; ?>/transparent=true/" seamless>
<a href="<?php echo $fallbackLink; ?>">
<?php echo $fallbackText; ?>
</a>
</iframe>
